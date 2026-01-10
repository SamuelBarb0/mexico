@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar con lista de conversaciones -->
        <div class="col-md-4 col-lg-3 border-end">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Conversaciones</h5>
                <a href="{{ route('inbox.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>

            <div class="list-group list-group-flush">
                @foreach($conversations as $conv)
                    @php
                        $lastMsg = $conv->messages->first();
                    @endphp
                    <a href="{{ route('inbox.show', $conv) }}"
                       class="list-group-item list-group-item-action {{ $conv->id === $contact->id ? 'active' : '' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    {{ $conv->name ?? $conv->phone }}
                                    @if($conv->unread_count > 0 && $conv->id !== $contact->id)
                                        <span class="badge bg-danger">{{ $conv->unread_count }}</span>
                                    @endif
                                </h6>
                                @if($lastMsg)
                                    <p class="mb-1 small text-truncate">
                                        {{ Str::limit($lastMsg->content, 30) }}
                                    </p>
                                @endif
                            </div>
                            <small>{{ $conv->last_message_at?->format('H:i') }}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Área principal de conversación -->
        <div class="col-md-8 col-lg-9">
            <!-- Header del contacto -->
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-3">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">
                                {{ $contact->name ?? $contact->phone }}
                            </h4>
                            <small class="text-muted">{{ $contact->phone }}</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person-lines-fill"></i> Ver Perfil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div class="messages-container" style="height: calc(100vh - 300px); overflow-y: auto;">
                @if($messages->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-chat" style="font-size: 3rem; color: #cbd5e0;"></i>
                        <p class="text-muted mt-3">No hay mensajes en esta conversación</p>
                    </div>
                @else
                    @foreach($messages as $message)
                        <div class="message mb-3 d-flex {{ $message->direction === 'outbound' ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message-bubble {{ $message->direction === 'outbound' ? 'outbound' : 'inbound' }}"
                                 style="max-width: 70%;">
                                <!-- Bubble header -->
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <small class="text-muted">
                                        @if($message->direction === 'inbound')
                                            <i class="bi bi-arrow-down-left"></i> Recibido
                                        @else
                                            <i class="bi bi-arrow-up-right"></i> Enviado
                                        @endif
                                    </small>
                                    @if($message->campaign)
                                        <span class="badge bg-info ms-2">
                                            <i class="bi bi-megaphone"></i> Campaña
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="message-content">
                                    {{ $message->content }}
                                </div>

                                <!-- Template info -->
                                @if($message->messageTemplate)
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-file-text"></i>
                                            Template: {{ $message->messageTemplate->name }}
                                        </small>
                                    </div>
                                @endif

                                <!-- Footer with timestamp and status -->
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    <span class="badge bg-{{
                                        $message->status === 'read' ? 'success' :
                                        ($message->status === 'delivered' ? 'info' :
                                        ($message->status === 'sent' ? 'warning' :
                                        ($message->status === 'failed' ? 'danger' : 'secondary')))
                                    }}">
                                        @if($message->direction === 'outbound')
                                            @if($message->status === 'read')
                                                <i class="bi bi-check2-all"></i> Leído
                                            @elseif($message->status === 'delivered')
                                                <i class="bi bi-check2-all"></i> Entregado
                                            @elseif($message->status === 'sent')
                                                <i class="bi bi-check2"></i> Enviado
                                            @elseif($message->status === 'failed')
                                                <i class="bi bi-x-circle"></i> Falló
                                            @else
                                                <i class="bi bi-clock"></i> {{ ucfirst($message->status) }}
                                            @endif
                                        @else
                                            <i class="bi bi-check2"></i> {{ ucfirst($message->status) }}
                                        @endif
                                    </span>
                                </div>

                                <!-- Error info if failed -->
                                @if($message->status === 'failed' && $message->error_message)
                                    <div class="alert alert-danger mt-2 mb-0" role="alert">
                                        <small>
                                            <strong>Error:</strong> {{ $message->error_message }}
                                            @if($message->error_code)
                                                (Código: {{ $message->error_code }})
                                            @endif
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Info de mensajes de plantilla -->
            <div class="alert alert-info mt-3" role="alert">
                <i class="bi bi-info-circle"></i>
                <strong>Nota:</strong> WhatsApp Business API solo permite enviar mensajes de plantilla aprobadas fuera de las ventanas de 24 horas.
                Los usuarios pueden responder libremente dentro de esa ventana.
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.messages-container {
    padding: 1rem;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-bubble.outbound {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.message-bubble.outbound .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.message-bubble.inbound {
    background: #f1f3f5;
    color: #212529;
}

.message-content {
    word-wrap: break-word;
    white-space: pre-wrap;
}

.list-group-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
}
</style>

@push('scripts')
<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>
@endpush
@endsection
