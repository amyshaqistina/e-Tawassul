<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crisis Case Receipt</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #2c3e50; font-size: 12px; }
        .header { border-bottom: 3px solid #1a6fa8; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #1a6fa8; margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; color: #6c757d; font-size: 11px; }
        h2 { color: #1a6fa8; font-size: 14px; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px; margin-top: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td { padding: 6px; vertical-align: top; }
        .label { background: #f0f7fc; color: #6c757d; width: 30%; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #6c757d; text-align: center; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; color: #fff; }
        .bg-active { background: #28a745; }
        .bg-pending { background: #ffc107; color: #000; }
        .bg-resolved { background: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h1>e-Tawassul Crisis Case Receipt</h1>
        <p>International Islamic University Malaysia &middot; Generated {{ $generatedAt->format('d M Y, h:i A') }}</p>
    </div>

    <h2>Case Summary</h2>
    <table>
        <tr><td class="label">Case ID</td><td>#{{ $crisis->crisis_id }}</td></tr>
        <tr><td class="label">Type</td><td>{{ ucwords(str_replace('_',' ', $crisis->crisis_type)) }}</td></tr>
        <tr><td class="label">Impact Level</td><td>{{ strtoupper($crisis->impact_level) }}</td></tr>
        <tr><td class="label">Status</td><td><span class="badge bg-{{ $crisis->status }}">{{ strtoupper($crisis->status) }}</span></td></tr>
        <tr><td class="label">Location</td><td>{{ $crisis->location ?? '—' }}</td></tr>
        <tr><td class="label">Date Reported</td><td>{{ $crisis->date_reported?->format('d M Y, h:i A') }}</td></tr>
    </table>

    <h2>Student</h2>
    <table>
        <tr><td class="label">Name</td><td>{{ $crisis->student?->full_name ?? '—' }}</td></tr>
        <tr><td class="label">Student ID</td><td>{{ $crisis->student?->student_id ?? '—' }}</td></tr>
        <tr><td class="label">Kulliyyah</td><td>{{ $crisis->student?->kulliyyah ?? '—' }}</td></tr>
    </table>

    <h2>Description</h2>
    <p>{{ $crisis->crisis_description }}</p>

    <h2>Funding Status</h2>
    <table>
        <tr><td class="label">Target</td><td>RM {{ number_format($crisis->donation_target, 2) }}</td></tr>
        <tr><td class="label">Raised</td><td>RM {{ number_format($crisis->donation_raised, 2) }}</td></tr>
        <tr><td class="label">Progress</td><td>{{ $crisis->progress_percent }}%</td></tr>
        <tr><td class="label">Unique Donors</td><td>{{ $donorCount }}</td></tr>
    </table>

    @php $verified = $crisis->reports->firstWhere('report_status', 'verified'); @endphp
    @if($verified && $verified->blockchain_hash)
        <h2>Blockchain Verification</h2>
        <table>
            <tr><td class="label">Hash (SHA-256)</td><td style="font-family: monospace; word-break: break-all;">{{ $verified->blockchain_hash }}</td></tr>
            <tr><td class="label">Verified</td><td>{{ $verified->verified_at?->format('d M Y, h:i A') }}</td></tr>
        </table>
    @endif

    <div class="footer">
        This document is computer-generated. For verification, present this receipt to the IIUM Student Welfare Office.<br>
        e-Tawassul &middot; Secure Blockchain-Based Crisis Response System &middot; IIUM
    </div>
</body>
</html>
