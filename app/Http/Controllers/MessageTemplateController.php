<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\WabaAccount;
use App\Services\Meta\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageTemplateController extends Controller
{
    protected TemplateService $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $query = MessageTemplate::where('tenant_id', $tenantId)
            ->with('wabaAccount')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $templates = $query->paginate(15);

        return view('templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $wabaAccounts = WabaAccount::where('tenant_id', $user->tenant_id)->get();

        return view('templates.create', compact('wabaAccounts'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        \Log::info('=== INICIO CREACIÓN DE PLANTILLA ===');
        \Log::info('Request data:', $request->all());

        try {
            // Decode components if it's a JSON string
            if ($request->has('components') && is_string($request->components)) {
                $componentsArray = json_decode($request->components, true);
                $request->merge(['components' => $componentsArray]);
                \Log::info('Components decodificado:', ['components' => $componentsArray]);
            }

            $validated = $request->validate([
                'waba_account_id' => 'required|exists:waba_accounts,id',
                'name' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
                'language' => 'required|string|size:2',
                'category' => 'required|in:MARKETING,UTILITY,AUTHENTICATION',
                'description' => 'nullable|string',
                'components' => 'required|array',
                'components.*.type' => 'required|in:HEADER,BODY,FOOTER,BUTTONS',
                'components.*.text' => 'nullable|string',
                'components.*.format' => 'nullable|string',
                'tags' => 'nullable|array',
            ], [
                'name.regex' => 'El nombre solo puede contener letras minúsculas, números y guiones bajos',
            ]);

            \Log::info('Validación exitosa:', $validated);

            $user = $request->user();
            \Log::info('Usuario:', ['id' => $user->id, 'tenant_id' => $user->tenant_id]);

            // Verify WABA account belongs to tenant
            $wabaAccount = WabaAccount::where('id', $validated['waba_account_id'])
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();

            \Log::info('WABA Account encontrada:', ['id' => $wabaAccount->id, 'name' => $wabaAccount->name]);

            $result = $this->templateService->createAndSubmitTemplate(
                $validated,
                $user->tenant_id
            );

            \Log::info('Resultado del servicio:', $result);

            if ($result['success']) {
                \Log::info('Plantilla creada exitosamente, redirigiendo...');
                return redirect()
                    ->route('templates.show', $result['template'])
                    ->with('success', $result['message']);
            }

            \Log::error('Error en la creación de plantilla:', ['error' => $result['error']]);
            return back()
                ->withInput()
                ->withErrors(['error' => $result['error']]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación:', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Excepción en store:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified template
     */
    public function show(MessageTemplate $template)
    {
        Gate::authorize('view', $template);

        $template->load('wabaAccount', 'tenant');

        return view('templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(MessageTemplate $template)
    {
        Gate::authorize('update', $template);

        // Only allow editing drafts
        if (!$template->isDraft()) {
            return redirect()
                ->route('templates.show', $template)
                ->withErrors(['error' => 'Solo se pueden editar plantillas en borrador']);
        }

        $wabaAccounts = WabaAccount::where('tenant_id', $template->tenant_id)->get();

        return view('templates.edit', compact('template', 'wabaAccounts'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, MessageTemplate $template)
    {
        Gate::authorize('update', $template);

        if (!$template->isDraft()) {
            return back()->withErrors(['error' => 'Solo se pueden editar plantillas en borrador']);
        }

        $validated = $request->validate([
            'description' => 'nullable|string',
            'components' => 'required|array',
            'tags' => 'nullable|array',
        ]);

        $template->update($validated);

        // Re-extract variables
        $template->variables = $template->extractVariables();
        $template->save();

        return redirect()
            ->route('templates.show', $template)
            ->with('success', 'Plantilla actualizada correctamente');
    }

    /**
     * Remove the specified template
     */
    public function destroy(MessageTemplate $template)
    {
        Gate::authorize('delete', $template);

        $result = $this->templateService->deleteTemplate($template);

        if ($result['success']) {
            return redirect()
                ->route('templates.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['error']]);
    }

    /**
     * Submit template to Meta for approval
     */
    public function submit(MessageTemplate $template)
    {
        Gate::authorize('update', $template);

        if (!$template->isDraft()) {
            return back()->withErrors(['error' => 'Solo se pueden enviar plantillas en borrador']);
        }

        $result = $this->templateService->submitToMeta($template);

        if ($result['success']) {
            return redirect()
                ->route('templates.show', $template)
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['error']]);
    }

    /**
     * Sync template status with Meta
     */
    public function sync(MessageTemplate $template)
    {
        Gate::authorize('view', $template);

        $result = $this->templateService->syncTemplateStatus($template);

        if ($result['success']) {
            return redirect()
                ->route('templates.show', $template)
                ->with('success', 'Estado sincronizado con Meta');
        }

        return back()->withErrors(['error' => $result['error']]);
    }

    /**
     * Sync all templates for a WABA account
     */
    public function syncAll(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'waba_account_id' => 'required|exists:waba_accounts,id',
        ]);

        $wabaAccount = WabaAccount::where('id', $validated['waba_account_id'])
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $result = $this->templateService->syncAllTemplates($wabaAccount, $user->tenant_id);

        if ($result['success']) {
            return back()->with('success', "Sincronizadas {$result['synced']} plantillas, creadas {$result['created']} nuevas");
        }

        return back()->withErrors(['error' => $result['error']]);
    }
}
