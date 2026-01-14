<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('tenant');

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        // Get tenants for filter dropdown
        $tenants = Tenant::orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => User::count(),
            'platform_admins' => User::where('user_type', 'platform_admin')->count(),
            'tenant_admins' => User::where('user_type', 'tenant_admin')->count(),
            'tenant_users' => User::where('user_type', 'tenant_user')->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'tenants', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        $timezones = timezone_identifiers_list();

        return view('admin.users.create', compact('tenants', 'timezones'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'user_type' => 'required|in:platform_admin,tenant_admin,tenant_user',
            'is_active' => 'boolean',
        ];

        // Platform admins don't need a tenant
        if ($request->user_type !== 'platform_admin') {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $validated = $request->validate($rules);

        // Platform admins don't have a tenant
        if ($request->user_type === 'platform_admin') {
            $validated['tenant_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['tenant', 'roles']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        $timezones = timezone_identifiers_list();

        return view('admin.users.edit', compact('user', 'tenants', 'timezones'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'user_type' => 'required|in:platform_admin,tenant_admin,tenant_user',
            'is_active' => 'boolean',
        ];

        // Platform admins don't need a tenant
        if ($request->user_type !== 'platform_admin') {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        // Password is optional on update
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $validated = $request->validate($rules);

        // Platform admins don't have a tenant
        if ($request->user_type === 'platform_admin') {
            $validated['tenant_id'] = null;
        }

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $message = $user->is_active
            ? 'Usuario activado exitosamente'
            : 'Usuario desactivado exitosamente';

        return back()->with('success', $message);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
