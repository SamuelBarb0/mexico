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
        $currentUser = auth()->user();
        $query = User::with('tenant');

        // Apply tenant scope based on user type
        if ($currentUser->user_type === 'platform_admin') {
            // Platform admins can see all users
            // No filter needed
        } else {
            // Tenant admins and tenant users can only see users from their own tenant
            $query->where('tenant_id', $currentUser->tenant_id);
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by tenant (only for platform admins)
        if ($request->filled('tenant_id') && $currentUser->user_type === 'platform_admin') {
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

        // Get tenants for filter dropdown (only for platform admins)
        $tenants = $currentUser->user_type === 'platform_admin'
            ? Tenant::orderBy('name')->get()
            : collect();

        // Statistics based on user scope
        if ($currentUser->user_type === 'platform_admin') {
            // Platform admin sees all stats
            $stats = [
                'total' => User::count(),
                'platform_admins' => User::where('user_type', 'platform_admin')->count(),
                'tenant_admins' => User::where('user_type', 'tenant_admin')->count(),
                'tenant_users' => User::where('user_type', 'tenant_user')->count(),
                'active' => User::where('is_active', true)->count(),
            ];
        } else {
            // Tenant admin/user sees only their tenant stats
            $stats = [
                'total' => User::where('tenant_id', $currentUser->tenant_id)->count(),
                'platform_admins' => 0, // Tenant users can't see platform admins
                'tenant_admins' => User::where('tenant_id', $currentUser->tenant_id)
                    ->where('user_type', 'tenant_admin')->count(),
                'tenant_users' => User::where('tenant_id', $currentUser->tenant_id)
                    ->where('user_type', 'tenant_user')->count(),
                'active' => User::where('tenant_id', $currentUser->tenant_id)
                    ->where('is_active', true)->count(),
            ];
        }

        return view('admin.users.index', compact('users', 'tenants', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $currentUser = auth()->user();

        // Get tenants based on user type
        if ($currentUser->user_type === 'platform_admin') {
            // Platform admins can create users in any tenant
            $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        } else {
            // Tenant admins can only create users in their own tenant
            $tenants = Tenant::where('id', $currentUser->tenant_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $timezones = timezone_identifiers_list();

        return view('admin.users.create', compact('tenants', 'timezones'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'user_type' => 'required|in:platform_admin,tenant_admin,tenant_user',
            'is_active' => 'boolean',
        ];

        // Only platform admins can create platform admins
        if ($request->user_type === 'platform_admin' && $currentUser->user_type !== 'platform_admin') {
            return back()->with('error', 'No tienes permisos para crear administradores de plataforma');
        }

        // Platform admins don't need a tenant
        if ($request->user_type !== 'platform_admin') {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        $validated = $request->validate($rules);

        // Tenant admins can only create users in their own tenant
        if ($currentUser->user_type !== 'platform_admin') {
            if ($request->user_type !== 'platform_admin' && $request->tenant_id != $currentUser->tenant_id) {
                return back()->with('error', 'Solo puedes crear usuarios en tu propio tenant');
            }
        }

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
        $currentUser = auth()->user();

        // Tenant admins can only view users from their own tenant
        if ($currentUser->user_type !== 'platform_admin' && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'No tienes permisos para ver este usuario');
        }

        $user->load(['tenant', 'roles']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();

        // Tenant admins can only edit users from their own tenant
        if ($currentUser->user_type !== 'platform_admin' && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'No tienes permisos para editar este usuario');
        }

        // Get tenants based on user type
        if ($currentUser->user_type === 'platform_admin') {
            $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        } else {
            $tenants = Tenant::where('id', $currentUser->tenant_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $timezones = timezone_identifiers_list();

        return view('admin.users.edit', compact('user', 'tenants', 'timezones'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Tenant admins can only update users from their own tenant
        if ($currentUser->user_type !== 'platform_admin' && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'No tienes permisos para actualizar este usuario');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'user_type' => 'required|in:platform_admin,tenant_admin,tenant_user',
            'is_active' => 'boolean',
        ];

        // Only platform admins can create/modify platform admins
        if ($request->user_type === 'platform_admin' && $currentUser->user_type !== 'platform_admin') {
            return back()->with('error', 'No tienes permisos para crear o modificar administradores de plataforma');
        }

        // Platform admins don't need a tenant
        if ($request->user_type !== 'platform_admin') {
            $rules['tenant_id'] = 'required|exists:tenants,id';
        }

        // Password is optional on update
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $validated = $request->validate($rules);

        // Tenant admins can only update users in their own tenant
        if ($currentUser->user_type !== 'platform_admin') {
            if ($request->user_type !== 'platform_admin' && $request->tenant_id != $currentUser->tenant_id) {
                return back()->with('error', 'Solo puedes actualizar usuarios en tu propio tenant');
            }
        }

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
        $currentUser = auth()->user();

        // Tenant admins can only toggle status of users from their own tenant
        if ($currentUser->user_type !== 'platform_admin' && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'No tienes permisos para modificar este usuario');
        }

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
        $currentUser = auth()->user();

        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        // Tenant admins can only delete users from their own tenant
        if ($currentUser->user_type !== 'platform_admin' && $user->tenant_id !== $currentUser->tenant_id) {
            abort(403, 'No tienes permisos para eliminar este usuario');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
