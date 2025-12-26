<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignMessagesJob;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Contact;
use App\Models\MessageTemplate;
use App\Models\WabaAccount;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['wabaAccount', 'messageTemplate']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Search by name
        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $wabaAccounts = WabaAccount::where('status', 'active')->orderBy('name')->get();
        $templates = MessageTemplate::where('status', 'APPROVED')->orderBy('name')->get();

        return view('campaigns.create', compact('wabaAccounts', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waba_account_id' => 'required|exists:waba_accounts,id',
            'message_template_id' => 'required|exists:message_templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:broadcast,drip,triggered',
            'target_audience' => 'required|array',
            'target_audience.type' => 'required|in:all,lists,tags,custom',
            'target_audience.lists' => 'nullable|array',
            'target_audience.tags' => 'nullable|array',
            'template_variables_mapping' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        // Determine initial status
        $status = $request->has('scheduled_at') ? 'scheduled' : 'draft';
        $validated['status'] = $status;

        // Create campaign
        $campaign = Campaign::create($validated);

        // If immediate execution is requested
        if ($request->has('execute_now') && $request->execute_now == '1') {
            return redirect()->route('campaigns.prepare', $campaign)
                ->with('success', 'Campaña creada. Preparando envío...');
        }

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaña creada exitosamente');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load(['wabaAccount', 'messageTemplate', 'messages']);

        // Calculate metrics
        $metrics = [
            'total' => $campaign->total_recipients,
            'sent' => $campaign->sent_count,
            'delivered' => $campaign->delivered_count,
            'read' => $campaign->read_count,
            'failed' => $campaign->failed_count,
            'pending' => $campaign->messages()->where('status', 'PENDING')->count(),
            'delivery_rate' => $campaign->getDeliveryRate(),
            'read_rate' => $campaign->getReadRate(),
        ];

        return view('campaigns.show', compact('campaign', 'metrics'));
    }

    public function edit(Campaign $campaign)
    {
        $wabaAccounts = WabaAccount::where('status', 'active')->orderBy('name')->get();
        return view('campaigns.edit', compact('campaign', 'wabaAccounts'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'waba_account_id' => 'required|exists:waba_accounts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:broadcast,drip,trigger',
            'status' => 'required|in:draft,scheduled,active,paused,running,completed,failed',
            'message_template' => 'required|string',
            'target_audience' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'started_at' => 'nullable|date',
        ]);

        // Process message_template
        $messageTemplate = json_decode($validated['message_template'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['message_template' => 'El formato JSON no es válido'])->withInput();
        }
        $validated['message_template'] = $messageTemplate;

        // Process target_audience
        if (isset($validated['target_audience']) && !empty($validated['target_audience'])) {
            $targetAudience = json_decode($validated['target_audience'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['target_audience' => 'El formato JSON no es válido'])->withInput();
            }
            $validated['target_audience'] = $targetAudience;
        }

        $campaign->update($validated);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaña actualizada exitosamente');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaña eliminada exitosamente');
    }

    /**
     * Prepare campaign by creating individual messages for all contacts
     */
    public function prepare(Campaign $campaign)
    {
        // Validate campaign can be prepared
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->withErrors(['error' => 'Esta campaña no puede ser preparada en su estado actual']);
        }

        // Validate template exists and is approved
        if (!$campaign->messageTemplate || $campaign->messageTemplate->status !== 'APPROVED') {
            return back()->withErrors(['error' => 'La plantilla de mensaje no está aprobada']);
        }

        // Get target contacts based on audience criteria
        $contacts = $this->getTargetContacts($campaign);

        if ($contacts->isEmpty()) {
            return back()->withErrors(['error' => 'No se encontraron contactos para esta campaña']);
        }

        // Create campaign messages for each contact
        $messagesCreated = 0;
        foreach ($contacts as $contact) {
            $messageBody = $this->renderMessageBody($campaign->messageTemplate, $contact, $campaign->template_variables_mapping);

            CampaignMessage::create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'phone_number' => $contact->phone,
                'message_body' => $messageBody,
                'status' => 'PENDING',
                'template_variables' => $this->prepareContactVariables($contact, $campaign->template_variables_mapping),
            ]);

            $messagesCreated++;
        }

        // Update campaign total recipients
        $campaign->update([
            'total_recipients' => $messagesCreated,
        ]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', "Campaña preparada. {$messagesCreated} mensajes listos para enviar.");
    }

    /**
     * Execute campaign by dispatching jobs to send messages
     */
    public function execute(Campaign $campaign)
    {
        // Validate campaign can be executed
        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            return back()->withErrors(['error' => 'Esta campaña no puede ser ejecutada en su estado actual']);
        }

        // Check if messages have been prepared
        $pendingMessages = $campaign->messages()->where('status', 'PENDING')->count();

        if ($pendingMessages === 0) {
            return back()->withErrors(['error' => 'No hay mensajes pendientes. Prepara la campaña primero.']);
        }

        // Dispatch job to send messages
        SendCampaignMessagesJob::dispatch($campaign, 50);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaña en ejecución. Los mensajes se enviarán en segundo plano.');
    }

    /**
     * Get target contacts based on campaign audience criteria
     */
    protected function getTargetContacts(Campaign $campaign): \Illuminate\Database\Eloquent\Collection
    {
        $targetAudience = $campaign->target_audience;
        $query = Contact::query();

        switch ($targetAudience['type']) {
            case 'all':
                // All contacts
                break;

            case 'lists':
                // Contacts from specific lists
                if (!empty($targetAudience['lists'])) {
                    $query->whereHas('lists', function ($q) use ($targetAudience) {
                        $q->whereIn('contact_lists.id', $targetAudience['lists']);
                    });
                }
                break;

            case 'tags':
                // Contacts with specific tags
                if (!empty($targetAudience['tags'])) {
                    $query->whereHas('tags', function ($q) use ($targetAudience) {
                        $q->whereIn('tags.id', $targetAudience['tags']);
                    });
                }
                break;

            case 'custom':
                // Custom filters (can be extended)
                if (!empty($targetAudience['filters'])) {
                    foreach ($targetAudience['filters'] as $filter) {
                        $query->where($filter['field'], $filter['operator'], $filter['value']);
                    }
                }
                break;
        }

        // Only active contacts with valid phone numbers
        $query->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        return $query->get();
    }

    /**
     * Render message body with variables replaced
     */
    protected function renderMessageBody(MessageTemplate $template, Contact $contact, ?array $mapping): string
    {
        $body = $template->components['body']['text'] ?? '';

        if (empty($mapping)) {
            return $body;
        }

        // Replace variables
        foreach ($mapping as $templateVar => $contactField) {
            $value = '';

            if ($contactField === 'custom') {
                // Custom values handled at send time
                continue;
            }

            // Get contact field value
            $value = $contact->{$contactField} ?? '';

            if ($contactField === 'name' && empty($value)) {
                $value = trim($contact->first_name . ' ' . $contact->last_name);
            }

            // Replace in body text (e.g., {{1}} -> value)
            $body = str_replace("{{" . substr($templateVar, -1) . "}}", $value, $body);
        }

        return $body;
    }

    /**
     * Prepare contact variables for storage
     */
    protected function prepareContactVariables(Contact $contact, ?array $mapping): array
    {
        $variables = [];

        if (empty($mapping)) {
            return $variables;
        }

        foreach ($mapping as $templateVar => $contactField) {
            if ($contactField !== 'custom') {
                $value = $contact->{$contactField} ?? '';

                if ($contactField === 'name' && empty($value)) {
                    $value = trim($contact->first_name . ' ' . $contact->last_name);
                }

                $variables[$templateVar] = $value;
            }
        }

        return $variables;
    }

    /**
     * Show campaign metrics/analytics
     */
    public function metrics(Campaign $campaign)
    {
        $campaign->load(['wabaAccount', 'messageTemplate']);

        // Get detailed message statistics
        $messageStats = [
            'pending' => $campaign->messages()->where('status', 'PENDING')->count(),
            'queued' => $campaign->messages()->where('status', 'QUEUED')->count(),
            'sent' => $campaign->messages()->where('status', 'SENT')->count(),
            'delivered' => $campaign->messages()->where('status', 'DELIVERED')->count(),
            'read' => $campaign->messages()->where('status', 'READ')->count(),
            'failed' => $campaign->messages()->where('status', 'FAILED')->count(),
        ];

        // Get recent failed messages
        $failedMessages = $campaign->messages()
            ->where('status', 'FAILED')
            ->with('contact')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate rates
        $metrics = [
            'delivery_rate' => $campaign->getDeliveryRate(),
            'read_rate' => $campaign->getReadRate(),
        ];

        return view('campaigns.metrics', compact('campaign', 'messageStats', 'failedMessages', 'metrics'));
    }
}
