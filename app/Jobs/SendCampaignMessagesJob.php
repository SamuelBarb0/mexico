<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCampaignMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected Campaign $campaign;
    protected int $batchSize;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign, int $batchSize = 50)
    {
        $this->campaign = $campaign;
        $this->batchSize = $batchSize;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        Log::info('Starting campaign message sending', [
            'campaign_id' => $this->campaign->id,
            'campaign_name' => $this->campaign->name,
        ]);

        // Mark campaign as active if it's scheduled
        if ($this->campaign->isScheduled()) {
            $this->campaign->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        // Get pending messages for this campaign
        $pendingMessages = CampaignMessage::where('campaign_id', $this->campaign->id)
            ->where('status', 'PENDING')
            ->limit($this->batchSize)
            ->get();

        if ($pendingMessages->isEmpty()) {
            Log::info('No pending messages found for campaign', [
                'campaign_id' => $this->campaign->id,
            ]);

            // Check if campaign is completed
            $this->checkCampaignCompletion();
            return;
        }

        $wabaAccount = $this->campaign->wabaAccount;
        $template = $this->campaign->messageTemplate;

        if (!$wabaAccount || !$template) {
            Log::error('Campaign missing WABA account or template', [
                'campaign_id' => $this->campaign->id,
                'has_waba' => !is_null($wabaAccount),
                'has_template' => !is_null($template),
            ]);
            return;
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($pendingMessages as $message) {
            try {
                // Mark as queued
                $message->markAsQueued();

                // Prepare variables for this message
                $variables = $this->prepareMessageVariables($message);

                // Send the message
                $result = $whatsAppService->sendTemplateMessage(
                    $wabaAccount,
                    $message->phone_number,
                    $template,
                    $variables
                );

                if ($result['success']) {
                    $message->markAsSent($result['message_id']);
                    $successCount++;

                    // Update campaign sent count
                    $this->campaign->increment('sent_count');
                } else {
                    $message->markAsFailed(
                        $result['error_message'],
                        $result['error_code'] ?? null
                    );
                    $failedCount++;

                    // Update campaign failed count
                    $this->campaign->increment('failed_count');
                }

                // Rate limiting: WhatsApp allows ~80 messages per second for marketing
                // Sleep for a bit to avoid hitting rate limits
                usleep(100000); // 0.1 seconds between messages
            } catch (\Exception $e) {
                Log::error('Exception sending campaign message', [
                    'campaign_message_id' => $message->id,
                    'phone_number' => $message->phone_number,
                    'exception' => $e->getMessage(),
                ]);

                $message->markAsFailed($e->getMessage(), 'EXCEPTION');
                $failedCount++;
                $this->campaign->increment('failed_count');
            }
        }

        Log::info('Campaign batch completed', [
            'campaign_id' => $this->campaign->id,
            'batch_size' => $pendingMessages->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);

        // Check if there are more messages to send
        $remainingMessages = CampaignMessage::where('campaign_id', $this->campaign->id)
            ->where('status', 'PENDING')
            ->count();

        if ($remainingMessages > 0) {
            // Dispatch another job for the next batch
            SendCampaignMessagesJob::dispatch($this->campaign, $this->batchSize)
                ->delay(now()->addSeconds(10)); // Delay 10 seconds between batches
        } else {
            // All messages processed, check if campaign is complete
            $this->checkCampaignCompletion();
        }
    }

    /**
     * Prepare variables for a specific message
     */
    protected function prepareMessageVariables(CampaignMessage $message): array
    {
        $variables = [];
        $templateVariables = $message->template_variables ?? [];
        $mapping = $this->campaign->template_variables_mapping ?? [];

        // Map contact fields to template variables based on campaign mapping
        $contact = $message->contact;

        foreach ($mapping as $templateVar => $contactField) {
            if ($contactField === 'custom') {
                // Use custom value from template_variables
                $variables[$templateVar] = $templateVariables[$templateVar] ?? '';
            } else {
                // Use contact field
                $value = $contact->{$contactField} ?? '';

                // Handle special cases
                if ($contactField === 'name' && empty($value)) {
                    $value = $contact->first_name . ' ' . $contact->last_name;
                }

                $variables[$templateVar] = $value;
            }
        }

        return $variables;
    }

    /**
     * Check if campaign is completed and update status
     */
    protected function checkCampaignCompletion(): void
    {
        $pendingCount = CampaignMessage::where('campaign_id', $this->campaign->id)
            ->where('status', 'PENDING')
            ->count();

        if ($pendingCount === 0) {
            $this->campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Campaign completed', [
                'campaign_id' => $this->campaign->id,
                'total_recipients' => $this->campaign->total_recipients,
                'sent_count' => $this->campaign->sent_count,
                'failed_count' => $this->campaign->failed_count,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendCampaignMessagesJob failed', [
            'campaign_id' => $this->campaign->id,
            'exception' => $exception->getMessage(),
        ]);

        // Mark campaign as failed or paused
        $this->campaign->update([
            'status' => 'paused',
        ]);
    }
}
