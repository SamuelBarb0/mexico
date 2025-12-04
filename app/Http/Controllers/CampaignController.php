<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\WabaAccount;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with('wabaAccount')->paginate(15);
        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $wabaAccounts = WabaAccount::where('status', 'active')->orderBy('name')->get();
        return view('campaigns.create', compact('wabaAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'waba_account_id' => 'required|exists:waba_accounts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:broadcast,drip,trigger',
            'status' => 'required|in:draft,scheduled,active,paused',
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

        $campaign = Campaign::create($validated);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaña creada exitosamente');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('wabaAccount');
        return view('campaigns.show', compact('campaign'));
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

    public function execute(Campaign $campaign)
    {
        // Basic validation
        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            return back()->withErrors(['error' => 'Esta campaña no puede ser ejecutada en su estado actual']);
        }

        // Update campaign status
        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        // TODO: Implement actual campaign execution logic
        // This would typically involve:
        // - Fetching contacts based on target_audience
        // - Sending messages via WhatsApp API
        // - Tracking delivery status
        // - Updating messages_sent, messages_delivered, messages_failed counters

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaña ejecutada exitosamente');
    }
}
