@extends('layouts.public')
@section('title', 'Donate to Case #' . $crisis->crisis_id)

{{--
    Receives from DonationController::create():
        $crisis      App\Models\Crisis (with student loaded)
        $isClosed    bool — true when donations are NOT being accepted
        $closedKind  string — 'goal_reached' or 'admin_closed'
--}}

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --d-bg: #f5f6fa;
        --d-card: #ffffff;
        --d-ink: #1a2238;
        --d-ink-soft: #5b6479;
        --d-ink-faint: #8a92a6;
        --d-border: #e8eaf0;
        --d-border-soft: #f0f2f7;
        --d-primary: #2563eb;
        --d-primary-soft: #dbe6ff;
        --d-primary-tint: #eef3ff;
        --d-primary-dark: #1d4ed8;
        --d-success: #15803d;
        --d-success-soft: #d4f0de;
        --d-success-tint: #e8f6ee;
        --d-amber: #b45309;
        --d-amber-soft: #fce4c2;
        --d-amber-tint: #fdf1de;
        --d-purple: #6d28d9;
        --d-purple-soft: #e0d4fb;
        --d-purple-tint: #f0e9fd;
        --d-danger: #b91c1c;
        --d-shadow: 0 1px 2px rgba(20,28,55,.04), 0 4px 16px rgba(20,28,55,.04);
    }

    body.public-layout { background: var(--d-bg) !important; }

    .donate-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        max-width: 1180px; margin: 0 auto;
        padding: 28px 24px 80px;
        color: var(--d-ink);
    }

    /* === Top row: 2-column header + progress === */
    .top-row {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .header-card, .progress-card, .scenario-card {
        background: var(--d-card);
        border-radius: 16px;
        box-shadow: var(--d-shadow);
        position: relative;
        overflow: hidden;
    }
    .header-card { padding: 28px 32px; }
    .header-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--d-success), #22c55e);
    }

    .badge-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .d-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11.5px; font-weight: 600;
        padding: 5px 11px; border-radius: 999px;
        letter-spacing: .02em;
    }
    .d-badge i { font-size: 13px; }
    .d-badge.verified { background: var(--d-success-tint); color: var(--d-success); }
    .d-badge.active   { background: var(--d-primary-tint); color: var(--d-primary); }
    .d-badge.closed   { background: #e5e7eb; color: #374151; }

    .header-card h1 {
        font-family: 'Inter', Georgia, serif;
        font-weight: 600; font-size: 34px;
        letter-spacing: -.02em;
        margin: 0 0 10px;
        color: var(--d-ink);
        line-height: 1.15;
    }
    .case-location {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--d-ink-soft); font-size: 14px; margin-bottom: 12px;
    }
    .case-location i { font-size: 14px; }
    .case-summary {
        color: var(--d-ink-soft); font-size: 15px; max-width: 720px;
        margin: 0; line-height: 1.6;
    }

    /* Progress card */
    .progress-card {
        padding: 24px 28px;
        display: flex; flex-direction: column; justify-content: center;
    }
    .progress-top {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-bottom: 12px; flex-wrap: wrap; gap: 6px;
    }
    .progress-label {
        color: var(--d-ink-faint); font-size: 12px; font-weight: 500;
        text-transform: uppercase; letter-spacing: .08em;
    }
    .progress-amount-line {
        font-family: 'Inter', Georgia, serif;
        font-size: 14px; color: var(--d-ink-soft);
    }
    .progress-amount-line strong {
        font-weight: 600; font-size: 22px;
        color: var(--d-ink); letter-spacing: -.01em;
    }
    .d-bar {
        height: 10px; background: var(--d-border-soft);
        border-radius: 999px; overflow: hidden; position: relative;
    }
    .d-bar-fill {
        height: 100%; width: 0;
        background: linear-gradient(90deg, var(--d-success), #22c55e);
        border-radius: 999px;
        transition: width 1.4s cubic-bezier(.22,.61,.36,1);
        position: relative;
    }
    .d-bar-fill::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);
        animation: d-shine 2.4s ease-in-out infinite;
    }
    @keyframes d-shine { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }
    .progress-meta {
        display: flex; justify-content: space-between;
        margin-top: 10px; font-size: 12.5px; color: var(--d-ink-faint);
    }

    /* === Payment method tabs === */
    .pay-tabs-wrap { margin-bottom: 18px; }
    .pay-tabs-header { padding: 22px 28px 0; }
    .pay-tabs-title {
        font-family: 'Inter', Georgia, serif;
        font-size: 22px; font-weight: 600;
        margin: 0 0 4px; letter-spacing: -.01em;
    }
    .pay-tabs-sub {
        color: var(--d-ink-soft); font-size: 14px; margin: 0 0 18px;
    }
    .pay-tabs {
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
        padding: 0 28px;
    }
    .pay-tab {
        background: var(--d-bg);
        border: 2px solid var(--d-border-soft);
        border-radius: 12px;
        padding: 16px 18px;
        cursor: pointer;
        transition: all .2s ease;
        display: flex; align-items: center; gap: 14px;
        text-align: left;
        font-family: inherit;
        width: 100%;
    }
    .pay-tab:hover {
        border-color: var(--d-primary-soft);
        background: var(--d-primary-tint);
    }
    .pay-tab.active {
        border-color: var(--d-primary);
        background: var(--d-primary-tint);
        box-shadow: 0 0 0 4px rgba(37,99,235,.08);
    }
    .pay-tab-icon {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; font-size: 18px;
    }
    .pay-tab-icon.bank { background: var(--d-success-tint); color: var(--d-success); }
    .pay-tab-icon.ezpay { background: var(--d-purple-tint); color: var(--d-purple); }
    .pay-tab-text { flex: 1; min-width: 0; }
    .pay-tab-name { font-weight: 600; font-size: 15px; color: var(--d-ink); }
    .pay-tab-desc { font-size: 12.5px; color: var(--d-ink-soft); margin-top: 2px; }
    .pay-tab-check {
        width: 20px; height: 20px; border-radius: 50%;
        border: 2px solid var(--d-border);
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        transition: all .2s ease;
    }
    .pay-tab.active .pay-tab-check {
        background: var(--d-primary); border-color: var(--d-primary);
    }
    .pay-tab.active .pay-tab-check::after {
        content: ""; width: 8px; height: 8px; border-radius: 50%; background: #fff;
    }

    /* Tab content panels */
    .tab-content { display: none; padding: 22px 28px 26px; }
    .tab-content.active { display: block; animation: d-fade .3s ease; }
    @keyframes d-fade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

    /* Bank panel */
    .bank-panel {
        background: linear-gradient(135deg, var(--d-success-tint) 0%, var(--d-success-soft) 100%);
        border-radius: 12px; padding: 22px 24px;
        border: 1px solid var(--d-success-soft);
        position: relative; overflow: hidden;
    }
    .bank-panel::before {
        content: ""; position: absolute;
        top: -40px; right: -40px; width: 160px; height: 160px;
        background: radial-gradient(circle, rgba(255,255,255,.5), transparent 70%);
        pointer-events: none;
    }
    .bank-panel-head {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 16px; position: relative;
    }
    .bank-panel-head i { color: var(--d-success); font-size: 16px; }
    .bank-panel-title {
        font-weight: 600; font-size: 13.5px; color: var(--d-success);
        text-transform: uppercase; letter-spacing: .05em;
    }
    .bank-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        position: relative;
    }
    .bank-field {
        background: rgba(255,255,255,.7);
        backdrop-filter: blur(8px);
        border-radius: 10px;
        padding: 13px 15px;
        border: 1px solid rgba(255,255,255,.8);
    }
    .bank-field-label {
        font-size: 11px; font-weight: 600;
        color: var(--d-ink-soft);
        text-transform: uppercase; letter-spacing: .06em;
        margin-bottom: 5px;
    }
    .bank-field-value {
        font-family: 'Inter', Georgia, serif;
        font-size: 17px; font-weight: 600; color: var(--d-ink);
        display: flex; align-items: center; justify-content: space-between;
        gap: 8px; letter-spacing: -.005em;
    }
    .bank-field-value .mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 15px;
    }
    .bank-pill-btn {
        background: #fff; border: 1px solid var(--d-border);
        color: var(--d-ink-soft);
        font-size: 11px; font-weight: 600;
        padding: 4px 9px; border-radius: 7px;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 4px;
        transition: all .15s ease;
        font-family: inherit;
    }
    .bank-pill-btn:hover {
        background: var(--d-primary); color: #fff; border-color: var(--d-primary);
    }
    .bank-pill-btn.copied {
        background: var(--d-success); color: #fff; border-color: var(--d-success);
    }
    .bank-pill-btn i { font-size: 11px; }

    .qr-section {
        margin-top: 14px;
        background: rgba(255,255,255,.7);
        border-radius: 10px; padding: 14px;
        display: flex; align-items: center; gap: 16px;
        border: 1px solid rgba(255,255,255,.8);
    }
    .qr-image-wrap {
        width: 90px; height: 90px;
        background: #fff;
        border-radius: 10px; padding: 4px;
        border: 1px solid var(--d-border);
        flex-shrink: 0;
        overflow: hidden;
    }
    .qr-image-wrap img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .qr-text { flex: 1; }
    .qr-title { font-weight: 600; font-size: 14px; color: var(--d-ink); margin: 0 0 2px; }
    .qr-sub { font-size: 12.5px; color: var(--d-ink-soft); margin: 0; }

    .verify-note {
        margin-top: 14px;
        display: flex; align-items: flex-start; gap: 9px;
        font-size: 12.5px; color: var(--d-ink-soft);
        padding: 10px 13px;
        background: rgba(255,255,255,.5);
        border-radius: 10px;
        border: 1px dashed var(--d-success);
        line-height: 1.5;
    }
    .verify-note i { color: var(--d-success); flex-shrink: 0; margin-top: 1px; font-size: 14px; }

    /* EzPay panel */
    .ezpay-panel {
        background: linear-gradient(135deg, var(--d-purple-tint) 0%, var(--d-purple-soft) 100%);
        border-radius: 12px; padding: 22px 24px;
        border: 1px solid var(--d-purple-soft);
        position: relative; overflow: hidden;
    }
    .ezpay-panel::before {
        content: ""; position: absolute;
        top: -40px; right: -40px; width: 160px; height: 160px;
        background: radial-gradient(circle, rgba(255,255,255,.5), transparent 70%);
        pointer-events: none;
    }
    .ezpay-head { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; position: relative; }
    .ezpay-head i { color: var(--d-purple); font-size: 16px; }
    .ezpay-title {
        font-weight: 600; font-size: 13.5px; color: var(--d-purple);
        text-transform: uppercase; letter-spacing: .05em;
    }
    .ezpay-desc {
        color: var(--d-ink); font-size: 14px; margin: 0 0 16px;
        background: rgba(255,255,255,.55);
        padding: 13px 15px; border-radius: 10px;
        position: relative; line-height: 1.55;
    }
    .ezpay-btn {
        display: inline-flex; align-items: center; gap: 9px;
        background: var(--d-purple); color: #fff; text-decoration: none;
        font-weight: 600; font-size: 14.5px;
        padding: 12px 20px; border-radius: 11px;
        transition: all .2s ease;
        box-shadow: 0 4px 12px rgba(109,40,217,.25);
        position: relative;
    }
    .ezpay-btn:hover {
        background: #5b21b6; color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(109,40,217,.35);
    }
    .ezpay-btn i { font-size: 14px; }
    .ezpay-steps {
        margin-top: 18px;
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
    }
    .ezpay-step {
        background: rgba(255,255,255,.7);
        border-radius: 10px; padding: 13px;
        border: 1px solid rgba(255,255,255,.8);
    }
    .ezpay-step-num {
        width: 24px; height: 24px; border-radius: 7px;
        background: var(--d-purple); color: #fff;
        font-weight: 700; font-size: 12px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 8px;
    }
    .ezpay-step-text { font-size: 12.5px; color: var(--d-ink); line-height: 1.45; }
    .ezpay-step-text .ref {
        background: #fff; border: 1px solid var(--d-purple-soft);
        padding: 1px 6px; border-radius: 5px;
        font-family: ui-monospace, Menlo, monospace;
        font-size: 11.5px; color: var(--d-purple); font-weight: 600;
    }

    /* No-bank notice */
    .no-bank-card {
        background: var(--d-card);
        border-radius: 16px; padding: 28px 32px;
        margin-bottom: 18px;
        box-shadow: var(--d-shadow);
        position: relative; overflow: hidden;
    }
    .no-bank-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--d-purple), #8b5cf6);
    }
    .no-bank-notice {
        background: var(--d-amber-tint);
        border: 1px solid var(--d-amber-soft);
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 22px;
        display: flex; align-items: flex-start; gap: 11px;
    }
    .no-bank-notice i { color: var(--d-amber); flex-shrink: 0; margin-top: 2px; font-size: 18px; }
    .no-bank-notice-text { font-size: 13.5px; color: var(--d-ink); line-height: 1.55; }
    .no-bank-notice-text strong { font-weight: 600; }

    /* Record donation form */
    .form-card {
        background: var(--d-card);
        border-radius: 16px;
        box-shadow: var(--d-shadow);
        padding: 28px 32px;
        margin-top: 18px;
        position: relative; overflow: hidden;
    }
    .form-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, var(--d-primary), #3b82f6);
    }
    .form-head { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 6px; }
    .form-icon {
        width: 46px; height: 46px; border-radius: 12px;
        background: var(--d-primary-tint); color: var(--d-primary);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        font-size: 22px;
    }
    .form-title {
        font-family: 'Inter', Georgia, serif;
        font-size: 22px; font-weight: 600; margin: 0 0 3px; letter-spacing: -.01em;
    }
    .form-desc { color: var(--d-ink-soft); font-size: 14px; margin: 0; }

    .field-group { margin-top: 20px; }
    .field-label {
        display: block; font-weight: 600; font-size: 13px;
        color: var(--d-ink); margin-bottom: 7px;
    }
    .field-label .req { color: var(--d-danger); margin-left: 2px; }
    .field-hint { font-size: 12px; color: var(--d-ink-faint); margin-top: 5px; }

    .amount-chips { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 10px; }
    .amount-chip {
        background: var(--d-bg); border: 1.5px solid var(--d-border);
        color: var(--d-ink); font-weight: 600; font-size: 14px;
        padding: 8px 16px; border-radius: 10px; cursor: pointer;
        transition: all .15s ease; font-family: inherit;
    }
    .amount-chip:hover { background: var(--d-primary-tint); border-color: var(--d-primary-soft); }
    .amount-chip.active { background: var(--d-primary); color: #fff; border-color: var(--d-primary); }

    .d-input, .d-select, .d-textarea {
        width: 100%; background: #fff;
        border: 1.5px solid var(--d-border);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px; color: var(--d-ink);
        font-family: inherit;
        transition: all .15s ease;
    }
    .d-input:focus, .d-select:focus, .d-textarea:focus {
        outline: none; border-color: var(--d-primary);
        box-shadow: 0 0 0 4px rgba(37,99,235,.1);
    }
    .d-textarea { resize: vertical; min-height: 80px; line-height: 1.5; }

    .amount-input-wrap { position: relative; }
    .amount-input-wrap::before {
        content: "RM"; position: absolute;
        left: 14px; top: 50%; transform: translateY(-50%);
        color: var(--d-ink-faint); font-weight: 600; font-size: 14px;
        pointer-events: none; z-index: 1;
    }
    .amount-input-wrap .d-input { padding-left: 42px; font-weight: 600; font-size: 15.5px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

    .checkbox-row {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 12px 14px;
        background: var(--d-bg); border-radius: 10px;
        cursor: pointer; transition: background .15s ease;
        margin-top: 18px;
    }
    .checkbox-row:hover { background: var(--d-primary-tint); }
    .checkbox-row input {
        margin: 0; width: 18px; height: 18px;
        accent-color: var(--d-primary);
        cursor: pointer; flex-shrink: 0; margin-top: 1px;
    }
    .checkbox-row-text { font-size: 13.5px; color: var(--d-ink); font-weight: 500; }
    .checkbox-row-sub { font-size: 12px; color: var(--d-ink-soft); margin-top: 1px; }

    .submit-btn {
        width: 100%;
        background: var(--d-primary); color: #fff;
        font-weight: 600; font-size: 15px;
        padding: 14px 22px;
        border: none; border-radius: 12px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        margin-top: 22px;
        transition: all .2s ease;
        box-shadow: 0 4px 12px rgba(37,99,235,.25);
        font-family: inherit;
    }
    .submit-btn:hover {
        background: var(--d-primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37,99,235,.35);
    }
    .submit-btn i { font-size: 16px; }

    /* === Dual payment actions (Billplz + manual) === */
    .pay-actions { margin-top: 22px; }
    .submit-btn-primary {
        margin-top: 0;
        background: linear-gradient(135deg, var(--d-purple), #8b5cf6);
        box-shadow: 0 4px 14px rgba(109,40,217,.3);
        font-size: 16px;
        padding: 16px 22px;
    }
    .submit-btn-primary:hover {
        background: linear-gradient(135deg, #5b21b6, #7c3aed);
        box-shadow: 0 6px 20px rgba(109,40,217,.4);
    }
    .submit-btn-secondary {
        margin-top: 0;
        background: var(--d-card);
        color: var(--d-ink);
        border: 2px solid var(--d-border);
        box-shadow: none;
        font-weight: 500;
    }
    .submit-btn-secondary:hover {
        background: var(--d-bg);
        border-color: var(--d-success);
        color: var(--d-success);
        box-shadow: 0 2px 8px rgba(20,28,55,.06);
    }
    .pay-divider {
        display: flex; align-items: center; gap: 14px;
        color: var(--d-ink-faint); font-size: 12px;
        margin: 16px 0;
        text-transform: uppercase; letter-spacing: .05em;
    }
    .pay-divider::before, .pay-divider::after {
        content: ""; flex: 1; height: 1px; background: var(--d-border);
    }
    .pay-divider span { white-space: nowrap; font-weight: 500; }

    /* Closed state */
    .closed-card {
        background: var(--d-card);
        border-radius: 16px;
        box-shadow: var(--d-shadow);
        padding: 40px 32px; margin-bottom: 18px;
        text-align: center;
        position: relative; overflow: hidden;
    }
    .closed-card::before {
        content: ""; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    }
    .closed-card.goal-reached::before { background: linear-gradient(90deg, var(--d-success), #22c55e); }
    .closed-card.admin-closed::before { background: linear-gradient(90deg, #6B7280, #9CA3AF); }

    .closed-icon {
        width: 72px; height: 72px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px; font-size: 34px;
    }
    .closed-icon.success { background: var(--d-success-tint); color: var(--d-success); }
    .closed-icon.neutral { background: #f3f4f6; color: #6b7280; }

    .closed-card h3 {
        font-family: 'Inter', Georgia, serif;
        font-size: 26px; font-weight: 600;
        color: var(--d-ink); margin: 0 0 10px;
        letter-spacing: -.015em;
    }
    .closed-card .closed-blurb {
        font-size: 14.5px; color: var(--d-ink-soft); line-height: 1.6;
        margin: 0 auto 24px; max-width: 540px;
    }
    .closed-stats {
        display: flex; justify-content: center; gap: 36px; flex-wrap: wrap;
        background: var(--d-bg); border-radius: 12px;
        padding: 20px 28px; margin: 0 auto 22px;
        max-width: 540px;
    }
    .closed-stat-num {
        font-family: 'Inter', Georgia, serif;
        font-size: 26px; font-weight: 600; color: var(--d-ink);
        line-height: 1;
    }
    .closed-stat-label {
        font-size: 11px; color: var(--d-ink-faint);
        text-transform: uppercase; letter-spacing: .05em;
        margin-top: 4px;
    }
    .closed-back-link {
        color: var(--d-primary); text-decoration: none;
        font-weight: 600; font-size: 14px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .closed-back-link:hover { text-decoration: underline; }

    .reveal {
        opacity: 0; transform: translateY(12px);
        animation: reveal .6s cubic-bezier(.22,.61,.36,1) forwards;
    }
    .reveal:nth-child(1){animation-delay:.05s}
    .reveal:nth-child(2){animation-delay:.12s}
    .reveal:nth-child(3){animation-delay:.19s}
    .reveal:nth-child(4){animation-delay:.26s}
    @keyframes reveal { to { opacity: 1; transform: translateY(0); } }

    .d-toast {
        position: fixed; bottom: 24px; left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: var(--d-ink); color: #fff;
        padding: 12px 20px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        transition: transform .3s cubic-bezier(.22,.61,.36,1);
        z-index: 1000;
        display: flex; align-items: center; gap: 8px;
    }
    .d-toast.show { transform: translateX(-50%) translateY(0); }
    .d-toast i { color: #22c55e; font-size: 16px; }

    .d-alert {
        padding: 12px 18px; border-radius: 12px;
        margin-bottom: 16px; font-size: 14px;
        display: flex; align-items: flex-start; gap: 10px;
    }
    .d-alert.success { background: var(--d-success-tint); color: var(--d-success); border: 1px solid var(--d-success-soft); }
    .d-alert.warning { background: var(--d-amber-tint); color: var(--d-amber); border: 1px solid var(--d-amber-soft); }
    .d-alert.danger  { background: #fee2e2; color: var(--d-danger); border: 1px solid #fecaca; }

    @media (max-width: 900px) {
        .top-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 700px) {
        .donate-page { padding: 18px 16px 60px; }
        .header-card, .no-bank-card, .form-card { padding: 22px 20px; }
        .progress-card { padding: 20px; }
        .pay-tabs-header { padding: 20px 20px 0; }
        .pay-tabs { padding: 0 20px; grid-template-columns: 1fr; }
        .tab-content { padding: 18px 20px 22px; }
        .header-card h1 { font-size: 26px; }
        .bank-grid, .grid-2 { grid-template-columns: 1fr; }
        .ezpay-steps { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="donate-page">

    @if(session('status'))
        <div class="d-alert success reveal"><i class="bi bi-check-circle-fill"></i>{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="d-alert warning reveal"><i class="bi bi-exclamation-triangle-fill"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="d-alert danger reveal">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                @foreach($errors->all() as $err) <div>{{ $err }}</div> @endforeach
            </div>
        </div>
    @endif

    <div class="top-row">
        <section class="header-card reveal">
            <div class="badge-row">
                <span class="d-badge verified"><i class="bi bi-shield-check"></i> Verified</span>
                @if($crisis->status === 'active' && !$isClosed)
                    <span class="d-badge active"><i class="bi bi-circle-fill"></i> Active</span>
                @endif
                @if($isClosed)
                    <span class="d-badge closed"><i class="bi bi-lock-fill"></i> Donations Closed</span>
                @endif
            </div>
            <h1>{{ ucwords(str_replace('_', ' ', $crisis->crisis_type)) }}</h1>
            @if($crisis->location)
                <div class="case-location"><i class="bi bi-geo-alt"></i> {{ $crisis->location }}</div>
            @endif
            <p class="case-summary">{{ \Illuminate\Support\Str::limit($crisis->crisis_description, 300) }}</p>
        </section>

        <section class="progress-card reveal">
            <div class="progress-top">
                <span class="progress-label">Raised</span>
                <span class="progress-amount-line">
                    <strong>RM {{ number_format((float) $crisis->donation_raised, 2) }}</strong>
                    of RM {{ number_format((float) $crisis->donation_target, 2) }}
                </span>
            </div>
            <div class="d-bar">
                <div class="d-bar-fill" id="progressFill" data-percent="{{ $crisis->progress_percent }}"></div>
            </div>
            <div class="progress-meta">
                <span>{{ $crisis->progress_percent }}% funded</span>
                <span>{{ $crisis->updated_at?->diffForHumans() ?? 'Just now' }}</span>
            </div>
        </section>
    </div>

    @if($isClosed)
        @if($closedKind === 'goal_reached')
            <div class="closed-card goal-reached reveal">
                <div class="closed-icon success"><i class="bi bi-check-circle-fill"></i></div>
                <h3>Goal reached — thank you!</h3>
                <p class="closed-blurb">
                    This case successfully raised
                    <strong>RM {{ number_format((float) $crisis->donation_raised, 2) }}</strong>
                    with the help of generous donors like you. Donations are no longer being collected for this case.
                </p>
                <div class="closed-stats">
                    <div>
                        <div class="closed-stat-num">RM {{ number_format((float) $crisis->donation_raised, 0) }}</div>
                        <div class="closed-stat-label">Total raised</div>
                    </div>
                    <div>
                        <div class="closed-stat-num">{{ $crisis->donations()->count() }}</div>
                        <div class="closed-stat-label">Donors</div>
                    </div>
                    <div>
                        <div class="closed-stat-num">{{ $crisis->donation_closed_at?->diffInDays($crisis->date_reported) ?? 0 }}d</div>
                        <div class="closed-stat-label">Campaign length</div>
                    </div>
                </div>
                <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="closed-back-link">
                    <i class="bi bi-arrow-right"></i> View case updates
                </a>
            </div>
        @else
            <div class="closed-card admin-closed reveal">
                <div class="closed-icon neutral"><i class="bi bi-lock-fill"></i></div>
                <h3>Donations are closed</h3>
                <p class="closed-blurb">
                    Donations are no longer being accepted for this case. Thank you to everyone who contributed —
                    <strong>RM {{ number_format((float) $crisis->donation_raised, 2) }}</strong>
                    was raised from {{ $crisis->donations()->count() }} {{ \Illuminate\Support\Str::plural('donor', $crisis->donations()->count()) }}.
                </p>
                <a href="{{ route('crisis.show', $crisis->crisis_id) }}" class="closed-back-link">
                    <i class="bi bi-arrow-right"></i> View case updates
                </a>
            </div>
        @endif

    @else
        @php
            $student = $crisis->student;
            $hasDirect = $student && ($student->bank_account_number || $student->qr_code_path);
        @endphp

        @if($hasDirect)
            <section class="scenario-card pay-tabs-wrap reveal">
                <div class="pay-tabs-header">
                    <h2 class="pay-tabs-title">Choose your donation method</h2>
                    <p class="pay-tabs-sub">Both options are verified by IIUM administration.</p>
                </div>

                <div class="pay-tabs">
                    <button type="button" class="pay-tab active" data-tab="bank" onclick="switchTab('bank')">
                        <div class="pay-tab-icon bank"><i class="bi bi-bank2"></i></div>
                        <div class="pay-tab-text">
                            <div class="pay-tab-name">Direct Bank Transfer</div>
                            <div class="pay-tab-desc">Straight to student's account</div>
                        </div>
                        <div class="pay-tab-check"></div>
                    </button>

                    <button type="button" class="pay-tab" data-tab="ezpay" onclick="switchTab('ezpay')">
                        <div class="pay-tab-icon ezpay"><i class="bi bi-credit-card-2-front"></i></div>
                        <div class="pay-tab-text">
                            <div class="pay-tab-name">IIUM ezPay Portal</div>
                            <div class="pay-tab-desc">Official university gateway</div>
                        </div>
                        <div class="pay-tab-check"></div>
                    </button>
                </div>

                <div class="tab-content active" id="tab-bank">
                    <div class="bank-panel">
                        <div class="bank-panel-head">
                            <i class="bi bi-shield-check"></i>
                            <span class="bank-panel-title">Verified Recipient Details</span>
                        </div>

                        <div class="bank-grid">
                            @if($student->bank_name)
                                <div class="bank-field">
                                    <div class="bank-field-label">Bank</div>
                                    <div class="bank-field-value"><span>{{ $student->bank_name }}</span></div>
                                </div>
                            @endif

                            @if($student->bank_account_holder)
                                <div class="bank-field">
                                    <div class="bank-field-label">Account Holder</div>
                                    <div class="bank-field-value">
                                        <span>{{ $student->bank_account_holder }}</span>
                                        <button type="button" class="bank-pill-btn"
                                                onclick="copyValue(this, '{{ addslashes($student->bank_account_holder) }}')">
                                            <i class="bi bi-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if($student->bank_account_number)
                                @php
                                    $acct = (string) $student->bank_account_number;
                                    $last4 = substr($acct, -4);
                                @endphp
                                <div class="bank-field" style="grid-column: 1 / -1">
                                    <div class="bank-field-label">Account Number</div>
                                    <div class="bank-field-value">
                                        <span class="mono" id="acctNum" data-masked="•••• •••• {{ $last4 }}" data-full="{{ $acct }}">
                                            •••• •••• {{ $last4 }}
                                        </span>
                                        <span style="display: flex; gap: 6px;">
                                            <button type="button" class="bank-pill-btn" id="acctToggle" onclick="toggleAcct()">
                                                <i class="bi bi-eye"></i> Show
                                            </button>
                                            <button type="button" class="bank-pill-btn"
                                                    onclick="copyValue(this, '{{ $acct }}')">
                                                <i class="bi bi-copy"></i> Copy
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($student->qr_code_path)
                            <div class="qr-section">
                                <div class="qr-image-wrap">
                                    <img src="{{ asset('storage/' . $student->qr_code_path) }}"
                                         alt="DuitNow QR code for {{ $student->full_name }}">
                                </div>
                                <div class="qr-text">
                                    <p class="qr-title"><i class="bi bi-qr-code"></i> DuitNow QR</p>
                                    <p class="qr-sub">Scan with your banking app to pay {{ $student->full_name }} directly.</p>
                                </div>
                            </div>
                        @endif

                        <div class="verify-note">
                            <i class="bi bi-shield-check"></i>
                            <span>This case and the recipient's identity have been verified by IIUM administration. The bank details above are confirmed by the student.</span>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="tab-ezpay">
                    <div class="ezpay-panel">
                        <div class="ezpay-head">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span class="ezpay-title">IIUM Official Payment Gateway</span>
                        </div>

                        <p class="ezpay-desc">
                            Donate through the <strong>official IIUM ezPay portal</strong> — a secure, university-managed channel.
                            Useful if you prefer to keep payment within IIUM's system or need an official institutional receipt.
                        </p>

                        <a href="https://ezpay.iium.edu.my/user/dashboard" target="_blank" rel="noopener" class="ezpay-btn">
                            Open IIUM ezPay Portal
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>

                        <div class="ezpay-steps">
                            <div class="ezpay-step">
                                <div class="ezpay-step-num">1</div>
                                <div class="ezpay-step-text">Open the portal and log in or proceed as a guest donor.</div>
                            </div>
                            <div class="ezpay-step">
                                <div class="ezpay-step-num">2</div>
                                <div class="ezpay-step-text">Select <strong>"Charity / Tabung Kebajikan"</strong> as your payment category.</div>
                            </div>
                            <div class="ezpay-step">
                                <div class="ezpay-step-num">3</div>
                                <div class="ezpay-step-text">Include reference <span class="ref">#{{ strtoupper(substr($crisis->crisis_type, 0, 3)) }}-{{ $crisis->crisis_id }}</span> and complete payment.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        @else
            <div class="no-bank-card reveal">
                <div class="no-bank-notice">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="no-bank-notice-text">
                        <strong>Direct donations not yet set up.</strong> This student hasn't shared their bank details with the platform yet. You can still support this case through the official IIUM ezPay portal below.
                    </div>
                </div>

                <div class="ezpay-panel">
                    <div class="ezpay-head">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span class="ezpay-title">IIUM Official Payment Gateway</span>
                    </div>
                    <p class="ezpay-desc">
                        Donate through the <strong>official IIUM ezPay portal</strong> — a secure, university-managed channel for charitable contributions.
                        Funds will be routed to the right recipient by IIUM administration.
                    </p>
                    <a href="https://ezpay.iium.edu.my/user/dashboard" target="_blank" rel="noopener" class="ezpay-btn">
                        Donate via IIUM ezPay
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <div class="ezpay-steps">
                        <div class="ezpay-step">
                            <div class="ezpay-step-num">1</div>
                            <div class="ezpay-step-text">Open the portal and log in or proceed as a guest donor.</div>
                        </div>
                        <div class="ezpay-step">
                            <div class="ezpay-step-num">2</div>
                            <div class="ezpay-step-text">Select <strong>"Charity / Tabung Kebajikan"</strong> as your payment category.</div>
                        </div>
                        <div class="ezpay-step">
                            <div class="ezpay-step-num">3</div>
                            <div class="ezpay-step-text">Include reference <span class="ref">#{{ strtoupper(substr($crisis->crisis_type, 0, 3)) }}-{{ $crisis->crisis_id }}</span> and complete payment.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section class="form-card reveal">
            <div class="form-head">
                <div class="form-icon"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <h2 class="form-title">Record your donation</h2>
                    <p class="form-desc">Once your transfer is complete, fill this in so the donation appears on the case page. You'll receive a PDF receipt by email.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('donate.store', $crisis->crisis_id) }}" id="donateForm">
                @csrf

                <div class="field-group">
                    <label class="field-label">Donation Amount (RM)<span class="req">*</span></label>
                    <div class="amount-chips">
                        @foreach([20, 50, 100, 250, 500, 1000] as $amt)
                            <button type="button" class="amount-chip {{ old('donation_amount', 100) == $amt ? 'active' : '' }}"
                                    data-amount="{{ $amt }}" onclick="setAmount(this, {{ $amt }})">RM {{ $amt }}</button>
                        @endforeach
                    </div>
                    <div class="amount-input-wrap">
                        <input type="number" name="donation_amount" id="donation_amount"
                               class="d-input" value="{{ old('donation_amount', 100) }}"
                               min="1" max="1000000" step="0.01" required oninput="clearChips()">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field-group">
                        <label class="field-label">Your Name<span class="req">*</span></label>
                        <input type="text" name="donor_name" class="d-input"
                               value="{{ old('donor_name') }}"
                               placeholder="e.g. Ahmad bin Abdullah" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Email<span class="req">*</span></label>
                        <input type="email" name="donor_email" class="d-input"
                               value="{{ old('donor_email') }}"
                               placeholder="you@example.com" required>
                        <div class="field-hint">Receipt will be sent here.</div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="field-group">
                        <label class="field-label">Payment Method<span class="req">*</span></label>
                        <select name="payment_method" id="payment_method" class="d-select" required>
                            <option value="bank_transfer" {{ old('payment_method', 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="duitnow_qr"    {{ old('payment_method') === 'duitnow_qr' ? 'selected' : '' }}>DuitNow QR</option>
                            <option value="FPX"           {{ old('payment_method') === 'FPX' ? 'selected' : '' }}>FPX / IIUM ezPay</option>
                            <option value="credit_card"   {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit / Debit Card</option>
                            <option value="wallet"        {{ old('payment_method') === 'wallet' ? 'selected' : '' }}>e-Wallet</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Bank Reference <span style="font-weight:400;color:var(--d-ink-faint)">(optional)</span></label>
                        <input type="text" name="transfer_reference" class="d-input"
                               value="{{ old('transfer_reference') }}"
                               placeholder="e.g. TXN20260517-9082">
                        <div class="field-hint">From your bank's confirmation slip.</div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Support Message <span style="font-weight:400;color:var(--d-ink-faint)">(optional)</span></label>
                    <textarea name="support_message" class="d-textarea" maxlength="1000"
                              placeholder="A short note of support…">{{ old('support_message') }}</textarea>
                </div>

                <label class="checkbox-row">
                    <input type="checkbox" name="anonymous" value="1" {{ old('anonymous') ? 'checked' : '' }}>
                    <div>
                        <div class="checkbox-row-text">Donate anonymously</div>
                        <div class="checkbox-row-sub">Your name will appear as "Anonymous Donor".</div>
                    </div>
                </label>

                @if($billplzEnabled ?? false)
                    {{-- Two payment paths: instant gateway OR manual record --}}
                    <div class="pay-actions">
                        <button type="submit" formaction="{{ route('donate.billplz.pay', $crisis->crisis_id) }}" class="submit-btn submit-btn-primary">
                            <i class="bi bi-lightning-charge-fill"></i>
                            Pay Now via Billplz (FPX / Card / e-Wallet)
                        </button>

                        <div class="pay-divider"><span>or, if you've already transferred</span></div>

                        <button type="submit" class="submit-btn submit-btn-secondary">
                            <i class="bi bi-check-circle"></i>
                            I've completed my transfer manually
                        </button>
                    </div>
                @else
                    {{-- Billplz not configured — single manual button --}}
                    <button type="submit" class="submit-btn">
                        <i class="bi bi-check-circle"></i>
                        I've completed my transfer
                    </button>
                @endif
            </form>
        </section>
    @endif

</div>

<div class="d-toast" id="dToast">
    <i class="bi bi-check-circle-fill"></i>
    <span id="dToastMsg">Copied to clipboard</span>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const fill = document.getElementById('progressFill');
            if (fill) fill.style.width = (fill.dataset.percent || 0) + '%';
        }, 300);
    });

    function switchTab(name) {
        document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`.pay-tab[data-tab="${name}"]`).classList.add('active');
        document.getElementById(`tab-${name}`).classList.add('active');

        const select = document.getElementById('payment_method');
        if (select) {
            if (name === 'bank') select.value = 'bank_transfer';
            else if (name === 'ezpay') select.value = 'FPX';
        }
    }

    function toggleAcct() {
        const el = document.getElementById('acctNum');
        const btn = document.getElementById('acctToggle');
        const isVisible = el.textContent.trim() === el.dataset.full;
        if (isVisible) {
            el.textContent = el.dataset.masked;
            btn.innerHTML = '<i class="bi bi-eye"></i> Show';
        } else {
            el.textContent = el.dataset.full;
            btn.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
        }
    }

    function copyValue(btn, value) {
        const original = btn.innerHTML;
        const doCopy = () => {
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
            showToast('Copied to clipboard');
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = original;
            }, 1800);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText(value).then(doCopy);
        } else {
            const ta = document.createElement('textarea');
            ta.value = value;
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); doCopy(); } catch(e) {}
            document.body.removeChild(ta);
        }
    }

    function setAmount(btn, amount) {
        document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('donation_amount').value = amount;
    }
    function clearChips() {
        document.querySelectorAll('.amount-chip').forEach(c => c.classList.remove('active'));
    }

    function showToast(msg) {
        const t = document.getElementById('dToast');
        document.getElementById('dToastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2000);
    }

    document.getElementById('donateForm')?.addEventListener('submit', function(e) {
        const btn = this.querySelector('.submit-btn');
        if (btn) {
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Recording...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        }
    });
</script>
@endpush
