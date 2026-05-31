
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4">Mailpit Viewer (Internal)</h2>
        <span class="badge {{ $status == 'Online' ? 'bg-success' : 'bg-danger' }}">
            {{ $status }}
        </span>
    </div>

    <div class="row" style="height: 75vh;">
        <!-- Sidebar: Message List -->
        <div class="col-md-4 border-end" style="overflow-y: auto; background: #f8f9fa;">
            <div class="list-group list-group-flush">
                @forelse($messages as $msg)
                <a href="#" class="list-group-item list-group-item-action p-3" 
                   onclick="viewEmail('{{ $msg['id'] }}')" 
                   style="cursor:pointer; border-left: 4px solid transparent;" 
                   id="msg-{{ $msg['id'] }}">
                    <div class="d-flex justify-content-between mb-1">
                        <strong class="text-truncate" style="max-width: 70%;">{{ $msg['from'] }}</strong>
                        <small class="text-muted">{{ $msg['date'] }}</small>
                    </div>
                    <div class="text-muted small text-truncate" style="max-width: 100%;">
                        {{ $msg['subject'] }}
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-muted">No messages found.</div>
                @endforelse
            </div>
        </div>

        <!-- Main: Message Content -->
        <div class="col-md-8 bg-white" id="email-content">
            <div class="p-5 text-center text-muted" style="margin-top: 20vh;">
                <i class="bi bi-envelope-open" style="font-size: 3rem;"></i>
                <p class="mt-3">Select an email from the list to preview the content.</p>
            </div>
        </div>
    </div>
</div>

<script>
async function viewEmail(id) {
    // Update active state
    document.querySelectorAll('.list-group-item').forEach(el => el.style.borderLeftColor = 'transparent');
    document.getElementById('msg-' + id).style.borderLeftColor = '#0d6efd';

    const contentDiv = document.getElementById('email-content');
    contentDiv.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading content...</p></div>';

    try {
        const response = await fetch('/admin/mailpit-view/' + id);
        const data = await response.json();

        if (data) {
            contentDiv.innerHTML = `
                <div class="p-4 border-bottom bg-light">
                    <h4 class="mb-1">${data.subject}</h4>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>From: ${data.from}</span>
                        <span>Date: ${data.date}</span>
                    </div>
                </div>
                <div class="p-4" style="white-space: pre-wrap; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    ${data.content || 'No content available.'}
                </div>
            `;
        } else {
            contentDiv.innerHTML = '<div class="p-5 text-center text-danger">Failed to load email content.</div>';
        }
    } catch (e) {
        contentDiv.innerHTML = '<div class="p-5 text-center text-danger">Error connecting to proxy.</div>';
    }
}
</script>
@endsection
