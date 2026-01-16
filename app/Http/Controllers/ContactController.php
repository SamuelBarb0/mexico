<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Client;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('client');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('whatsapp_id', 'like', "%{$search}%");
            });
        }

        // Client filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(15);
        $clients = Client::orderBy('name')->get();

        return view('contacts.index', compact('contacts', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('contacts.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'whatsapp_id' => 'nullable|string|max:100|unique:contacts,whatsapp_id',
            'tags' => 'nullable|string',
            'custom_fields' => 'nullable|string',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        // Process tags
        if (isset($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Process custom_fields
        if (isset($validated['custom_fields'])) {
            $customFields = json_decode($validated['custom_fields'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['custom_fields' => 'El formato JSON no es válido'])->withInput();
            }
            $validated['custom_fields'] = $customFields;
        }

        $contact = Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contacto creado exitosamente');
    }

    public function show(Contact $contact)
    {
        $contact->load('client');
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $clients = Client::orderBy('name')->get();
        return view('contacts.edit', compact('contact', 'clients'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'whatsapp_id' => 'nullable|string|max:100|unique:contacts,whatsapp_id,' . $contact->id,
            'tags' => 'nullable|string',
            'custom_fields' => 'nullable|string',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        // Process tags
        if (isset($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Process custom_fields
        if (isset($validated['custom_fields'])) {
            $customFields = json_decode($validated['custom_fields'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['custom_fields' => 'El formato JSON no es válido'])->withInput();
            }
            $validated['custom_fields'] = $customFields;
        }

        $contact->update($validated);

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'Contacto actualizado exitosamente');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contacto eliminado exitosamente');
    }
}
