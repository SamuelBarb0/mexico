<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ContactController extends BaseApiController
{
    /**
     * List all contacts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (!$tenant) {
            return $this->error('Tenant no encontrado', 404);
        }

        $query = Contact::where('tenant_id', $tenant->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tag filter
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $contacts = $query->paginate($perPage);

        return $this->paginated($contacts, 'Contactos obtenidos exitosamente');
    }

    /**
     * Get a single contact
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $contact = Contact::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            return $this->error('Contacto no encontrado', 404);
        }

        return $this->success($contact);
    }

    /**
     * Create a new contact
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        if (!$tenant) {
            return $this->error('Tenant no encontrado', 404);
        }

        // Check subscription limits
        if ($tenant->hasReachedLimit('contacts')) {
            return $this->error(
                'Ha alcanzado el límite de contactos de su plan. Por favor, actualice su plan.',
                403
            );
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('contacts')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                }),
            ],
            'email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:5',
            'status' => 'nullable|string|in:active,inactive,blocked',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['status'] = $validated['status'] ?? 'active';

        $contact = Contact::create($validated);

        return $this->success($contact, 'Contacto creado exitosamente', 201);
    }

    /**
     * Update a contact
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $contact = Contact::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            return $this->error('Contacto no encontrado', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('contacts')->where(function ($query) use ($tenant) {
                    return $query->where('tenant_id', $tenant->id);
                })->ignore($contact->id),
            ],
            'email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:5',
            'status' => 'nullable|string|in:active,inactive,blocked',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'custom_fields' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $contact->update($validated);

        return $this->success($contact, 'Contacto actualizado exitosamente');
    }

    /**
     * Delete a contact
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $contact = Contact::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$contact) {
            return $this->error('Contacto no encontrado', 404);
        }

        $contact->delete();

        return $this->success(null, 'Contacto eliminado exitosamente');
    }

    /**
     * Bulk import contacts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $request->validate([
            'contacts' => 'required|array|min:1|max:1000',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone' => 'required|string|max:20',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.tags' => 'nullable|array',
        ]);

        $contacts = $request->contacts;
        $remaining = $tenant->getRemainingLimit('contacts');

        if (count($contacts) > $remaining) {
            return $this->error(
                "Solo puede importar {$remaining} contactos más según su plan actual.",
                403
            );
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($contacts as $index => $contactData) {
            // Check if phone already exists
            $exists = Contact::where('tenant_id', $tenant->id)
                ->where('phone', $contactData['phone'])
                ->exists();

            if ($exists) {
                $skipped++;
                $errors[] = "Contacto #{$index}: El teléfono {$contactData['phone']} ya existe";
                continue;
            }

            Contact::create([
                'tenant_id' => $tenant->id,
                'name' => $contactData['name'],
                'phone' => $contactData['phone'],
                'email' => $contactData['email'] ?? null,
                'tags' => $contactData['tags'] ?? [],
                'status' => 'active',
            ]);

            $imported++;
        }

        return $this->success([
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ], "Importación completada: {$imported} contactos importados, {$skipped} omitidos");
    }
}
