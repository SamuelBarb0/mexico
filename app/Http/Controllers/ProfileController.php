<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Mostrar el perfil del usuario autenticado
     */
    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    /**
     * Actualizar información del perfil
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado exitosamente');
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Contraseña actualizada exitosamente');
    }
}