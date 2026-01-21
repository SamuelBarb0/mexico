<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Message;
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
    public $timeout = 600; // 10 minutes for large campaigns

    protected Campaign $campaign;
    protected int $batchSize;
    protected bool $runSync;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign, int $batchSize = 50, bool $runSync = false)
    {
        $this->campaign = $campaign;
        $this->batchSize = $batchSize;
        $this->runSync = $runSync;
    }

    /**
     * Create and run synchronously (for shared hosting)
     */
    public static function runSynchronously(Campaign $campaign, int $batchSize = 50): void
    {
        $job = new self($campaign, $batchSize, true);
        app()->call([$job, 'handle']);
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

                // Create Message record in queued status
                $messageRecord = Message::create([
                    'tenant_id' => $this->campaign->tenant_id,
                    'waba_account_id' => $wabaAccount->id,
                    'contact_id' => $message->contact_id,
                    'campaign_id' => $this->campaign->id,
                    'message_template_id' => $template->id,
                    'direction' => 'outbound',
                    'type' => 'template',
                    'content' => $this->buildMessageContent($template, $variables),
                    'template_data' => [
                        'template_name' => $template->name,
                        'template_language' => $template->language,
                        'variables' => $variables,
                    ],
                    'status' => 'queued',
                ]);

                // Send the message
                $result = $whatsAppService->sendTemplateMessage(
                    $wabaAccount,
                    $message->phone_number,
                    $template,
                    $variables
                );

                if ($result['success']) {
                    // Update Message record with Meta message ID and mark as sent
                    $messageRecord->update([
                        'meta_message_id' => $result['message_id'],
                        'wamid' => $result['message_id'],
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    $message->markAsSent($result['message_id']);
                    $successCount++;

                    // Update campaign sent count
                    $this->campaign->increment('sent_count');

                    Log::info('Campaign message sent and tracked', [
                        'campaign_id' => $this->campaign->id,
                        'message_id' => $messageRecord->id,
                        'meta_message_id' => $result['message_id'],
                        'phone_number' => $message->phone_number,
                    ]);
                } else {
                    // Mark Message record as failed
                    $messageRecord->markAsFailed(
                        $result['error_code'] ?? null,
                        $result['error_message']
                    );

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

                // Mark Message record as failed if it was created
                if (isset($messageRecord)) {
                    $messageRecord->markAsFailed('EXCEPTION', $e->getMessage());
                }

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
            if ($this->runSync) {
                // Running synchronously - just log, let controller handle next batch call
                Log::info('Sync batch completed, remaining messages', [
                    'campaign_id' => $this->campaign->id,
                    'remaining' => $remainingMessages,
                ]);
                // Don't recurse - let the frontend call again for next batch
            } else {
                // Dispatch another job for the next batch (async mode)
                SendCampaignMessagesJob::dispatch($this->campaign, $this->batchSize)
                    ->delay(now()->addSeconds(10));
            }
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

        // Add header media URL if campaign has one
        if (!empty($this->campaign->header_media_url)) {
            $variables['header_media_url'] = $this->campaign->header_media_url;
        }

        return $variables;
    }

    /**
     * Build human-readable message content from template and variables
     */
    protected function buildMessageContent(\App\Models\MessageTemplate $template, array $variables): string
    {
        $content = '';
        $components = $this->normalizeComponents($template->components);

        // Add header if present
        if (isset($components['header'])) {
            $header = $components['header'];
            $format = $header['format'] ?? 'TEXT';

            if ($format === 'TEXT' && isset($header['text'])) {
                $headerText = $header['text'];
                // Replace variables in header
                foreach ($variables as $key => $value) {
                    if (strpos($key, 'header_') === 0) {
                        $index = str_replace('header_', '', $key);
                        $headerText = str_replace("{{{$index}}}", $value, $headerText);
                    }
                }
                $content .= $headerText . "\n\n";
            } elseif (in_array($format, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                $content .= "[{$format}]\n\n";
            }
        }

        // Add body (always present)
        if (isset($components['body']['text'])) {
            $bodyText = $components['body']['text'];
            // Replace variables in body
            foreach ($variables as $key => $value) {
                if (strpos($key, 'body_') === 0) {
                    $index = str_replace('body_', '', $key);
                    $bodyText = str_replace("{{{$index}}}", $value, $bodyText);
                }
            }
            $content .= $bodyText;
        }

        // Add footer if present
        if (isset($components['footer']['text'])) {
            $content .= "\n\n" . $components['footer']['text'];
        }

        // Add buttons if present
        if (isset($components['buttons']) && !empty($components['buttons'])) {
            $content .= "\n\n[Buttons: ";
            $buttonTexts = array_map(fn($btn) => $btn['text'] ?? '', $components['buttons']);
            $content .= implode(', ', $buttonTexts) . "]";
        }

        return $content;
    }

    /**
     * Normalize template components from Meta's array format to object format
     */
    protected function normalizeComponents(array $components): array
    {
        // If already in object format, return as-is
        if (isset($components['body']) || isset($components['header'])) {
            return $components;
        }

        $normalized = [];

        foreach ($components as $component) {
            if (!is_array($component) || !isset($component['type'])) {
                continue;
            }

            $type = strtoupper($component['type']);

            switch ($type) {
                case 'HEADER':
                    $normalized['header'] = [
                        'format' => $component['format'] ?? 'TEXT',
                        'text' => $component['text'] ?? '',
                    ];
                    break;
                case 'BODY':
                    $normalized['body'] = [
                        'text' => $component['text'] ?? '',
                    ];
                    break;
                case 'FOOTER':
                    $normalized['footer'] = [
                        'text' => $component['text'] ?? '',
                    ];
                    break;
                case 'BUTTONS':
                    $normalized['buttons'] = $component['buttons'] ?? [];
                    break;
            }
        }

        return $normalized;
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
