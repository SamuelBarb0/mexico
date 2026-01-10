@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="bi bi-chat-dots"></i> Inbox / Conversaciones
            </h1>
            <p class="text-muted">Gestiona todas tus conversaciones de WhatsApp</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($conversations->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-chat-dots" style="font-size: 4rem; color: #cbd5e0;"></i>
                        </div>
                        <h4>No hay conversaciones</h4>
                        <p class="text-muted">
                            Las conversaciones aparecerán aquí cuando envíes o recibas mensajes de tus contactos.
                        </p>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-megaphone"></i> Crear una Campaña
                        </a>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Contacto</th>
                                        <th>Último Mensaje</th>
                                        <th>Total Mensajes</th>
                                        <th>Sin Leer</th>
                                        <th>Última Actividad</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conversations as $contact)
                                        @php
                                            $lastMessage = $contact->messages->first();
                                        @endphp
                                        <tr class="{{ $contact->unread_count > 0 ? 'table-active fw-bold' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ $contact->name ?? $contact->phone }}
                                                                                                                    </div>
                                                        <small class="text-muted">
                                                            {{ $contact->phone }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($lastMessage)
                                                    <div class="text-truncate" style="max-width: 300px;">
                                                        @if($lastMessage->direction === 'inbound')
                                                            <i class="bi bi-arrow-down-left text-success"></i>
                                                        @else
                                                            <i class="bi bi-arrow-up-right text-primary"></i>
                                                        @endif
                                                        {{ Str::limit($lastMessage->content, 50) }}
                                                    </div>
                                                    <small class="text-muted">
                                                        <span class="badge bg-{{
                                                            $lastMessage->status === 'read' ? 'success' :
                                                            ($lastMessage->status === 'delivered' ? 'info' :
                                                            ($lastMessage->status === 'sent' ? 'warning' :
                                                            ($lastMessage->status === 'failed' ? 'danger' : 'secondary')))
                                                        }}">
                                                            {{ ucfirst($lastMessage->status) }}
                                                        </span>
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $contact->messages_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($contact->unread_count > 0)
                                                    <span class="badge bg-danger">
                                                        {{ $contact->unread_count }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $contact->last_message_at?->diffForHumans() ?? 'N/A' }}
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('inbox.show', $contact) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-chat"></i> Ver Conversación
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $conversations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}
</style>
@endsection
