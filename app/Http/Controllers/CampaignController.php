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
        $clients = \App\Models\Client::orderBy('name')->get();

        // Get unique tags from contacts
        $allTags = \App\Models\Contact::whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('campaigns.create', compact('wabaAccounts', 'templates', 'clients', 'allTags'));
    }

    public function store(Request $request)
    {
        \Log::info('=== INICIO: Creación de campaña ===');
        \Log::info('Request completo:', $request->all());
        \Log::info('Usuario autenticado:', [
            'id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        try {
            \Log::info('Iniciando validación...');
            $validated = $request->validate([
                'waba_account_id' => 'required|exists:waba_accounts,id',
                'message_template_id' => 'required|exists:message_templates,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:broadcast,drip,triggered',
                'target_audience.type' => 'required|in:all,client,tags,status,custom',
                'target_audience.client_id' => 'nullable|exists:clients,id',
                'target_audience.status' => 'nullable|in:active,inactive,blocked',
                'target_audience.tags' => 'nullable|array',
                'target_audience.tags.*' => 'nullable|string',
                'template_variables_mapping' => 'nullable|array',
                'scheduled_at' => 'nullable|date|after:now',
            ]);
            \Log::info('Validación exitosa:', $validated);

            // Build target_audience array
            $targetAudience = [
                'type' => $request->input('target_audience.type', 'all'),
                'client_id' => $request->input('target_audience.client_id'),
                'status' => $request->input('target_audience.status'),
                'tags' => $request->input('target_audience.tags', []),
            ];
            \Log::info('Target audience construido:', $targetAudience);

            // Determine initial status
            $status = $request->filled('scheduled_at') ? 'scheduled' : 'draft';
            \Log::info('Status determinado:', ['status' => $status]);

            // Prepare campaign data
            $campaignData = [
                'tenant_id' => auth()->user()->tenant_id,
                'waba_account_id' => $validated['waba_account_id'],
                'message_template_id' => $validated['message_template_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'status' => $status,
                'target_audience' => $targetAudience,
                'template_variables_mapping' => $validated['template_variables_mapping'] ?? [],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ];
            \Log::info('Datos de campaña preparados:', $campaignData);

            // Create campaign
            \Log::info('Intentando crear campaña en la base de datos...');
            $campaign = Campaign::create($campaignData);
            \Log::info('Campaña creada exitosamente:', [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
            ]);

            // If immediate execution is requested
            if ($request->has('execute_now') && $request->execute_now == '1') {
                \Log::info('Ejecución inmediata solicitada, redirigiendo a prepare');
                return redirect()->route('campaigns.prepare', $campaign)
                    ->with('success', 'Campaña creada. Preparando envío...');
            }

            \Log::info('=== FIN: Campaña creada exitosamente, redirigiendo a show ===');
            return redirect()->route('campaigns.show', $campaign)
                ->with('success', 'Campaña creada exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en creación de campaña:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error al crear campaña:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la campaña: ' . $e->getMessage());
        }
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
        \Log::info('=== INICIO: Preparación de campaña ===', [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'campaign_status' => $campaign->status,
        ]);

        // Validate campaign can be prepared
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            \Log::warning('Campaña no puede ser preparada - estado inválido', [
                'campaign_id' => $campaign->id,
                'status' => $campaign->status,
            ]);
            return back()->withErrors(['error' => 'Esta campaña no puede ser preparada en su estado actual']);
        }

        // Validate template exists and is approved
        if (!$campaign->messageTemplate || $campaign->messageTemplate->status !== 'APPROVED') {
            \Log::error('Template no aprobado o no existe', [
                'campaign_id' => $campaign->id,
                'template_id' => $campaign->message_template_id,
                'template_status' => $campaign->messageTemplate?->status,
            ]);
            return back()->withErrors(['error' => 'La plantilla de mensaje no está aprobada']);
        }

        \Log::info('Obteniendo contactos objetivo...');
        // Get target contacts based on audience criteria
        $contacts = $this->getTargetContacts($campaign);
        \Log::info('Contactos obtenidos:', [
            'total' => $contacts->count(),
            'target_audience' => $campaign->target_audience,
        ]);

        if ($contacts->isEmpty()) {
            \Log::warning('No se encontraron contactos para la campaña');
            return back()->withErrors(['error' => 'No se encontraron contactos para esta campaña']);
        }

        // Create campaign messages for each contact
        $messagesCreated = 0;
        \Log::info('Creando mensajes de campaña...');
        foreach ($contacts as $contact) {
            try {
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

                if ($messagesCreated % 10 == 0) {
                    \Log::info("Mensajes creados: {$messagesCreated}");
                }
            } catch (\Exception $e) {
                \Log::error('Error creando mensaje para contacto', [
                    'contact_id' => $contact->id,
                    'contact_phone' => $contact->phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \Log::info('Total de mensajes creados:', ['count' => $messagesCreated]);

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
    public function execute(Request $request, Campaign $campaign)
    {
        \Log::info('=== INICIO: Ejecución de campaña ===', [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'campaign_status' => $campaign->status,
        ]);

        // Validate campaign can be executed
        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            \Log::warning('Campaña no puede ser ejecutada - estado inválido', [
                'campaign_id' => $campaign->id,
                'status' => $campaign->status,
            ]);
            return back()->withErrors(['error' => 'Esta campaña no puede ser ejecutada en su estado actual']);
        }

        // Check if messages have been prepared
        $pendingMessages = $campaign->messages()->where('status', 'PENDING')->count();
        \Log::info('Mensajes pendientes encontrados:', ['count' => $pendingMessages]);

        if ($pendingMessages === 0) {
            \Log::warning('No hay mensajes pendientes para enviar');
            return back()->withErrors(['error' => 'No hay mensajes pendientes. Prepara la campaña primero.']);
        }

        // Dispatch job to queue
        \Log::info('Despachando job SendCampaignMessagesJob', [
            'campaign_id' => $campaign->id,
            'batch_size' => 50,
        ]);

        SendCampaignMessagesJob::dispatch($campaign, 50);

        // Process the queue in background
        \Log::info('Iniciando procesamiento de queue en segundo plano...');

        // Check if request is AJAX
        if ($request->expectsJson() || $request->wantsJson()) {
            // Start queue processing in background
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows - ejecutar en segundo plano
                pclose(popen("start /B php artisan queue:work --once --tries=3 2>&1", "r"));
            } else {
                // Linux/Unix
                exec("php artisan queue:work --once --tries=3 > /dev/null 2>&1 &");
            }

            \Log::info('=== FIN: Job despachado, procesamiento iniciado ===');
            return response()->json(['success' => true, 'message' => 'Campaña en ejecución']);
        }

        // Si no es AJAX (fallback), procesar sincrónicamente
        try {
            \Artisan::call('queue:work', [
                '--once' => true,
                '--tries' => 3,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Error procesando queue:', ['error' => $e->getMessage()]);
        }

        \Log::info('=== FIN: Job despachado y procesado ===');
        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaña ejecutada exitosamente.');
    }

    /**
     * Get campaign execution progress
     */
    public function progress(Campaign $campaign)
    {
        $total = $campaign->total_recipients;
        $pending = $campaign->messages()->where('status', 'PENDING')->count();
        $sent = $campaign->messages()->whereIn('status', ['SENT', 'DELIVERED', 'READ'])->count();
        $failed = $campaign->messages()->where('status', 'FAILED')->count();

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'sent' => $sent,
            'failed' => $failed,
        ]);
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
