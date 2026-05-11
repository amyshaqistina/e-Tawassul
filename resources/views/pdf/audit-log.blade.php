<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blockchain Audit Log</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #2c3e50; font-size: 10px; }
        .header { border-bottom: 2px solid #1a6fa8; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { color: #1a6fa8; margin: 0; font-size: 18px; }
        .header p { margin: 2px 0 0; color: #6c757d; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 9px; }
        th { background: #1a6fa8; color: #fff; text-align: left; padding: 6px; }
        td { padding: 5px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        tr:nth-child(even) td { background: #fafbfc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 8px; font-size: 8px; color: #fff; }
        .bg-quorum { background: #28a745; }
        .bg-sim { background: #ffc107; color: #000; }
        code { font-family: monospace; font-size: 9px; }
        .footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #ddd; font-size: 9px; color: #6c757d; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>e-Tawassul Blockchain Audit Log</h1>
        <p>Generated {{ $generatedAt->format('d M Y, h:i A') }} by {{ $admin?->admin_name ?? 'System' }} &middot; {{ count($records) }} records</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:13%;">Timestamp</th>
                <th style="width:18%;">Event Type</th>
                <th style="width:13%;">Reference</th>
                <th style="width:8%;">Mode</th>
                <th style="width:43%;">SHA-256 Hash</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $r)
                <tr>
                    <td>{{ $r->blockchain_id }}</td>
                    <td>{{ $r->timestamp?->format('d M Y H:i:s') }}</td>
                    <td>{{ $r->data_from }}</td>
                    <td>
                        @if($r->reference_table)
                            {{ $r->reference_table }}#{{ $r->reference_id }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($r->mode === 'quorum')
                            <span class="badge bg-quorum">QUORUM</span>
                        @else
                            <span class="badge bg-sim">SIM</span>
                        @endif
                    </td>
                    <td><code>{{ $r->stored_data }}</code></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        This audit log is cryptographically verifiable. Each entry's hash can be independently re-verified against
        the original event data via the e-Tawassul system. Permissioned chain: Quorum private network.
    </div>
</body>
</html>
