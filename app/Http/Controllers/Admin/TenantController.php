<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants with stats
     */
    public function index(Request $request)
    {
        $query = Tenant::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $tenants = $query->with(['users', 'subscriptions' => function($q) {
                $q->whereIn('status', ['trial', 'active'])->latest()->limit(1);
            }, 'subscriptions.plan'])
            ->withCount(['users', 'contacts', 'campaigns', 'wabaAccounts'])
            ->latest()
            ->paginate(20);

        // Overall statistics
        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'inactive' => Tenant::where('status', 'inactive')->count(),
            'total_users' => DB::table('users')->count(),
            'total_contacts' => DB::table('contacts')->count(),
            'total_campaigns' => DB::table('campaigns')->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'stats'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'billing_email' => 'required|email|max:255',
            'billing_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,inactive',
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'billing_email' => $request->billing_email,
            'billing_name' => $request->billing_name,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', 'Tenant creado exitosamente');
    }

    /**
     * Display tenant details with comprehensive statistics
     */
    public function show(Tenant $tenant)
    {
        $tenant->load([
            'users',
            'contacts',
            'campaigns',
            'wabaAccounts',
            'subscriptions.plan'
        ]);

        // Detailed statistics
        $stats = [
            'users' => [
                'total' => $tenant->users()->count(),
                'active' => $tenant->users()->whereNull('deleted_at')->count(),
            ],
            'contacts' => [
                'total' => $tenant->contacts()->count(),
                'with_messages' => $tenant->contacts()->whereHas('messages')->count(),
            ],
            'campaigns' => [
                'total' => $tenant->campaigns()->count(),
                'completed' => $tenant->campaigns()->where('status', 'completed')->count(),
                'failed' => $tenant->campaigns()->where('status', 'failed')->count(),
            ],
            'messages' => [
                'total' => DB::table('messages')->where('tenant_id', $tenant->id)->count(),
                'sent' => DB::table('messages')->where('tenant_id', $tenant->id)->where('direction', 'outbound')->count(),
                'received' => DB::table('messages')->where('tenant_id', $tenant->id)->where('direction', 'inbound')->count(),
            ],
            'waba_accounts' => $tenant->wabaAccounts()->count(),
        ];

        // Recent activity
        $recentCampaigns = $tenant->campaigns()->latest()->limit(5)->get();
        $recentUsers = $tenant->users()->latest()->limit(5)->get();

        return view('admin.tenants.show', compact('tenant', 'stats', 'recentCampaigns', 'recentUsers'));
    }

    /**
     * Show the form for editing a tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update tenant information
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id,
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,trial',
            'timezone' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'enable_trial' => 'nullable|boolean',
            'trial_days' => 'nullable|integer|min:1',
        ]);

        // Update basic fields
        $tenant->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'domain' => $validated['domain'] ?? null,
            'status' => $validated['status'],
        ]);

        // Update settings (timezone and language)
        $settings = $tenant->settings ?? [];

        if (isset($validated['timezone'])) {
            $settings['timezone'] = $validated['timezone'];
        }

        if (isset($validated['language'])) {
            $settings['language'] = $validated['language'];
        }

        $tenant->update(['settings' => $settings]);

        // Handle trial extension/activation
        if ($request->boolean('enable_trial')) {
            $trialDays = $validated['trial_days'] ?? 30;

            // If there's an existing trial, extend it
            if ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) {
                $newTrialEnd = $tenant->trial_ends_at->addDays($trialDays);
            } else {
                // Start a new trial from now
                $newTrialEnd = now()->addDays($trialDays);
            }

            $tenant->update(['trial_ends_at' => $newTrialEnd]);
        }

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant actualizado exitosamente');
    }

    /**
     * Toggle tenant status (activate/suspend)
     */
    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $newStatus]);

        $message = $newStatus === 'active'
            ? 'Tenant activado exitosamente'
            : 'Tenant suspendido exitosamente';

        return back()->with('success', $message);
    }

    /**
     * Soft delete a tenant
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant eliminado exitosamente');
    }
}
