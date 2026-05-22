@extends('layouts.student')
@section('title', 'My Last Digital Messages')
@section('page-title', 'Last Digital Messages')
@section('page-subtitle', 'Encrypted messages for your next of kin')

@push('styles')
<style>
    .ldms-intro-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .ldms-intro-text {
        flex: 1;
        min-width: 280px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 12px 14px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .ldms-intro-text i {
        font-size: 16px;
        color: #1a56db;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .ldms-intro-text p {
        margin: 0;
        font-size: 12.5px;
        color: #1e3a8a;
        line-height: 1.5;
    }
    .ldms-new-btn {
        background: #1a56db;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .ldms-new-btn:hover { background: #1245b8; color: #fff; }

    /* Card */
    .ldms-list-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    /* Empty state */
    .ldms-empty {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    .ldms-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #eff6ff;
        color: #1a56db;
        font-size: 30px;
        margin-bottom: 14px;
    }
    .ldms-empty h6 {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px 0;
    }
    .ldms-empty p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }

    /* Table */
    .ldms-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    .ldms-table thead th {
        background: #f8faff;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .ldms-table tbody td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: #0f172a;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .ldms-table tbody tr:last-child td { border-bottom: none; }
    .ldms-table tbody tr:hover { background: #f8faff; }
    .ldms-table .id-cell {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }

    /* Type badge */
    .ldms-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #1a56db;
        font-size: 11.5px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 8px;
        text-transform: capitalize;
    }
    .ldms-type-badge i { font-size: 13px; }

    /* Status pill */
    .ldms-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .ldms-status-pill.held { background: #fef3c7; color: #92400e; }
    .ldms-status-pill.released { background: #d1fae5; color: #065f46; }

    /* Action buttons */
    .ldms-action-row {
        display: inline-flex;
        gap: 6px;
        flex-wrap: nowrap;
        align-items: center;
    }
    .ldms-btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 7px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
        border: 1.5px solid;
        background: #fff;
    }
    .ldms-btn-view { color: #64748b; border-color: #e2e8f0; }
    .ldms-btn-view:hover { color: #475569; background: #f8fafc; }
    .ldms-btn-edit { color: #1a56db; border-color: #bfdbfe; }
    .ldms-btn-edit:hover { color: #fff; background: #1a56db; }
    .ldms-btn-delete { color: #dc2626; border-color: #fecaca; padding: 6px 9px; }
    .ldms-btn-delete:hover { color: #fff; background: #dc2626; }
    .ldms-locked-label {
        font-size: 11.5px;
        color: #94a3b8;
        font-style: italic;
    }

    @media (max-width: 700px) {
        .ldms-table thead { display: none; }
        .ldms-table tbody td { display: block; padding: 8px 14px; border: none; }
        .ldms-table tbody tr {
            display: block;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .ldms-table tbody td.text-end { text-align: left !important; padding-top: 10px; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Intro + New Message --}}
    <div class="ldms-intro-row">
        <div class="ldms-intro-text">
            <i class="bi bi-shield-lock-fill"></i>
            <p>
                Messages here are encrypted at rest. They are only released to your registered next of kin
                once a verified death confirmation has been recorded.
            </p>
        </div>
        <a href="{{ route('student.ldms.create') }}" class="ldms-new-btn">
            <i class="bi bi-plus-circle-fill"></i> New Message
        </a>
    </div>

    {{-- List card --}}
    <div class="ldms-list-card">
        @if($messages->isEmpty())
            <div class="ldms-empty">
                <div class="ldms-empty-icon">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
                <h6>No messages yet</h6>
                <p>Create your first message to be securely stored for your next of kin.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="ldms-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Last updated</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $m)
                            @php
                                $typeIcon = match($m->media_type) {
                                    'text'     => 'bi-pencil-square',
                                    'audio'    => 'bi-mic-fill',
                                    'image'    => 'bi-image-fill',
                                    'document' => 'bi-file-earmark-text-fill',
                                    'video'    => 'bi-camera-video-fill',
                                    'mixed'    => 'bi-collection-fill',
                                    default    => 'bi-envelope-fill',
                                };
                                $typeLabel = match($m->media_type) {
                                    'text'     => 'Written',
                                    'audio'    => 'Voice',
                                    'image'    => 'Photos',
                                    'document' => 'Document',
                                    'video'    => 'Video',
                                    'mixed'    => 'Mixed',
                                    default    => ucfirst($m->media_type),
                                };
                            @endphp
                            <tr>
                                <td class="id-cell">#{{ str_pad($m->ldms_id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <span class="ldms-type-badge">
                                        <i class="bi {{ $typeIcon }}"></i> {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>{{ $m->updated_at?->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($m->is_released)
                                        <span class="ldms-status-pill released">
                                            <i class="bi bi-unlock-fill"></i>
                                            Released {{ $m->date_triggered?->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="ldms-status-pill held">
                                            <i class="bi bi-shield-lock-fill"></i> Held
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="ldms-action-row">
                                        <a href="{{ route('student.ldms.show', $m->ldms_id) }}"
                                           class="ldms-btn-sm ldms-btn-view">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @if(!$m->is_released)
                                            <a href="{{ route('student.ldms.edit', $m->ldms_id) }}"
                                               class="ldms-btn-sm ldms-btn-edit">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('student.ldms.destroy', $m->ldms_id) }}"
                                                  class="d-inline m-0" onsubmit="return confirm('Permanently delete this message?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="ldms-btn-sm ldms-btn-delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="ldms-locked-label">
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div style="padding: 14px 16px; border-top: 1px solid #f1f5f9;">
                    {{ $messages->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
