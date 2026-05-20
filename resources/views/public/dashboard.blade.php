@extends('layouts.public')
@section('title', 'e-Tawassul — Student Crisis & Digital Legacy System')

@php
    /* Fallbacks so the page renders even if controller hasn't been updated */
$casesSupported = $stats['cases_supported'] ?? ($stats['total_active'] ?? 489);
$capacityGoal = $stats['capacity_goal'] ?? 760;
$totalRaised = $stats['total_raised'] ?? 184500;
$studentsProtected = $stats['students_protected'] ?? ($stats['total_supporters'] ?? 612);
$totalHelped = $stats['students_helped'] ?? 2487;
$totalSupporters = $stats['total_supporters'] ?? 6342;
$legacyMessages = $stats['legacy_messages'] ?? 892;
    $progressPct = $capacityGoal > 0 ? min(100, ($casesSupported / $capacityGoal) * 100) : 0;
@endphp

@push('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&family=Dancing+Script:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* All styles scoped under .etw- prefixes so they don't fight Bootstrap. */
        .etw {
            --primary: #1d4ed8;
            --primary-dark: #1e3a8a;
            --primary-light: #dbeafe;
            --primary-tint: #eff6ff;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --ink: #0f172a;
            --ink-soft: #334155;
            --muted: #64748b;
            --muted-soft: #94a3b8;
            --border: #e2e8f0;
            --border-soft: #f1f5f9;
            --bg-deep: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
            line-height: 1.6;
        }

        .etw a {
            color: inherit;
            text-decoration: none;
        }

        .etw-stage {
            position: relative;
            padding: 8px 24px 8px;
            background: radial-gradient(900px 500px at 50% -10%, rgba(29, 78, 216, 0.07), transparent 60%), linear-gradient(180deg, #f5f8ff 0%, #ffffff 100%);
            overflow: hidden;
        }

        /* Firefox doesn't fully support zoom — fall back to font-based scaling */
        @supports not (zoom: 1) {
            .etw-stage {
                zoom: normal;
                transform-origin: top center;
            }
        }

        .etw-stage::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(29, 78, 216, 0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 40%, transparent 30%, black 80%);
            -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 40%, transparent 30%, black 80%);
            pointer-events: none;
            opacity: 0.6;
        }

        .etw-stage__inner {
            max-width: 1280px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            min-height: 450px;
        }

        .etw-arc {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            opacity: 0.5;
        }

        .etw-arc path {
            fill: none;
            stroke: #93c5fd;
            stroke-width: 1.2;
            stroke-dasharray: 4 6;
        }

        .etw-circle {
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            z-index: 3;
            animation: etw-bob 6s ease-in-out infinite;
        }

        .etw-circle .ring {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px dashed rgba(29, 78, 216, 0.55);
        }

        .etw-circle .photo {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            border: 4px solid white;
            box-shadow: 0 16px 32px -14px rgba(15, 23, 42, 0.4);
            transition: transform .35s cubic-bezier(.22, .61, .36, 1);
        }

        .etw-circle:hover .photo {
            transform: scale(1.07);
        }

        .etw-circle .chip {
            position: absolute;
            top: 50%;
            background: white;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 8px 20px -8px rgba(15, 23, 42, 0.22);
            border: 1px solid #eef2f7;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            transition: transform .25s;
            color: var(--ink);
        }

        .etw-circle .chip i {
            color: var(--primary);
            font-size: 11px;
        }

        .etw-circle.l .chip {
            right: calc(100% + 12px);
            transform: translateY(-50%);
        }

        .etw-circle.l:hover .chip {
            transform: translate(-4px, -50%);
        }

        .etw-circle.r .chip {
            left: calc(100% + 12px);
            transform: translateY(-50%);
        }

        .etw-circle.r:hover .chip {
            transform: translate(4px, -50%);
        }

        .etw-circle.c1 {
            top: 20px;
            left: 190px;
            animation-delay: 0s;
        }

        .etw-circle.c2 {
            top: 170px;
            left: 130px;
            animation-delay: .6s;
        }

        .etw-circle.c3 {
            top: 320px;
            left: 190px;
            animation-delay: 1.2s;
        }

        .etw-circle.c4 {
            top: 20px;
            right: 190px;
            animation-delay: .3s;
        }

        .etw-circle.c5 {
            top: 170px;
            right: 130px;
            animation-delay: .9s;
        }

        .etw-circle.c6 {
            top: 320px;
            right: 190px;
            animation-delay: 1.5s;
        }

        @keyframes etw-bob {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .etw-headline {
            text-align: center;
            max-width: 720px;
            margin: 0 auto;
            position: relative;
            z-index: 5;
            padding: 4px 0 0;
        }

        .etw-headline h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 3.2vw, 42px);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin: 0;
        }

        .etw-headline h1 em {
            font-style: normal;
            color: var(--primary);
            background: linear-gradient(180deg, transparent 65%, rgba(29, 78, 216, 0.14) 65%);
            padding: 0 4px;
        }

        .etw-headline .lead {
            margin-top: 8px;
            font-size: 13px;
            color: var(--muted);
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.5;
        }

        .etw-clock {
            margin: 6px auto 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 9px;
            background: white;
            border: 1px solid var(--border-soft);
            border-radius: 999px;
            font-size: 10px;
            color: var(--muted);
            box-shadow: 0 4px 12px -6px rgba(15, 23, 42, 0.08);
        }

        .etw-clock .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
            animation: etw-pulse 1.6s infinite;
        }

        @keyframes etw-pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .4;
                transform: scale(1.4);
            }
        }

        .etw-stats {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 18px;
            margin-top: 6px;
            flex-wrap: wrap;
            position: relative;
            z-index: 5;
        }

        .etw-stat {
            text-align: center;
            min-width: 52px;
        }

        .etw-stat .n {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .etw-stat .l {
            font-size: 9px;
            font-weight: 700;
            color: var(--muted);
            margin-top: 2px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .etw-stat-sep {
            width: 1px;
            height: 18px;
            background: linear-gradient(180deg, transparent, var(--border), transparent);
        }

        .etw-progress-wrap {
            margin: 8px auto 0;
            max-width: 380px;
            position: relative;
            z-index: 5;
            text-align: center;
        }

        .etw-progress-wrap h2 {
            font-family: 'Dancing Script', cursive;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .etw-progress-card {
            background: white;
            border-radius: 12px;
            padding: 8px 14px 6px;
            box-shadow: 0 12px 28px -14px rgba(29, 78, 216, 0.25), 0 2px 6px rgba(15, 23, 42, 0.04);
            border: 1px solid #e6ecff;
        }

        .etw-progress-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 4px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .etw-progress-counter {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .etw-progress-counter .big {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .etw-progress-counter .of {
            font-size: 12px;
            color: var(--muted-soft);
            font-weight: 700;
        }

        .etw-progress-pct {
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            font-weight: 700;
            color: var(--success);
        }

        .etw-progress-bar {
            position: relative;
            height: 6px;
            background: var(--border-soft);
            border-radius: 999px;
            overflow: hidden;
        }

        .etw-progress-fill {
            position: absolute;
            inset: 0 auto 0 0;
            width: 0%;
            background: linear-gradient(90deg, #2563eb, #06b6d4);
            border-radius: 999px;
            transition: width 1.8s cubic-bezier(.22, .61, .36, 1);
            box-shadow: 0 2px 8px rgba(29, 78, 216, 0.4);
        }

        .etw-progress-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
            font-size: 9px;
            color: var(--muted);
        }

        .etw-progress-meta strong {
            color: var(--ink);
            font-weight: 700;
        }

        .etw-progress-row {
            display: grid;
            grid-template-columns: 1fr;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid var(--border-soft);
        }

        .etw-progress-cell {
            text-align: center;
        }

        .etw-progress-cell .v {
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            font-weight: 800;
        }

        .etw-progress-cell .v.success {
            color: var(--success);
        }

        .etw-progress-cell .v.warning {
            color: var(--warning);
        }

        .etw-progress-cell .k {
            font-size: 8px;
            color: var(--muted);
            margin-top: 1px;
        }

        .etw-cta-row {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 36px;
            flex-wrap: wrap;
            position: relative;
            z-index: 5;
        }

        .etw-btn {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            transition: transform .2s, box-shadow .2s, background .2s, color .2s, border-color .2s;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none !important;
        }

        .etw-btn .arrow {
            transition: transform .25s;
        }

        .etw-btn:hover .arrow {
            transform: translateX(4px);
        }

        .etw-btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            box-shadow: 0 10px 24px -8px rgba(29, 78, 216, 0.55);
        }

        .etw-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -8px rgba(29, 78, 216, 0.7);
            color: white;
        }

        .etw-btn-ghost {
            background: white;
            color: var(--ink);
            border-color: var(--border);
        }

        .etw-btn-ghost:hover {
            color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .etw .etw-btn-solid,
        a.etw-btn-solid {
            background: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px -4px rgba(29, 78, 216, 0.4);
            padding: 11px 24px !important;
            font-size: 13px !important;
            border-radius: 10px !important;
            gap: 10px;
            border: 2px solid transparent !important;
        }

        .etw .etw-btn-solid:hover,
        a.etw-btn-solid:hover {
            background: #1e3a8a !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px -6px rgba(29, 78, 216, 0.55);
        }

        .etw .etw-btn-solid i,
        a.etw-btn-solid i {
            font-size: 13px !important;
            color: #ffffff !important;
        }

        .etw-btn-outline {
            background: white;
            color: var(--ink);
            border: 2px solid var(--border);
            padding: 12px 30px;
            font-size: 15px;
        }

        .etw-btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .etw-btn-outline i {
            font-size: 14px;
            color: var(--primary);
        }

        .etw-trust-section {
            padding: 4px 24px 8px;
            background: white;
        }

        .etw-trust {
            margin: 0 auto;
            max-width: 1180px;
            background: white;
            border-radius: 10px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 6px 16px -12px rgba(29, 78, 216, 0.15);
            padding: 5px 12px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .etw-trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .etw-trust-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-tint);
            color: var(--primary);
            font-size: 9px;
        }

        .etw-trust-item h4 {
            font-size: 10px;
            font-weight: 700;
            margin: 0;
            color: var(--ink);
            line-height: 1.15;
        }

        .etw-trust-item p {
            font-size: 8px;
            color: var(--muted);
            margin: 0;
            line-height: 1.15;
        }

        .etw-block {
            padding: 80px 24px;
            max-width: 1280px;
            margin: 0 auto;
        }

        .etw-block-head {
            text-align: center;
            max-width: 720px;
            margin: 0 auto 56px;
        }

        .etw-tag {
            display: inline-block;
            padding: 5px 14px;
            background: var(--primary-tint);
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-radius: 999px;
            margin-bottom: 14px;
        }

        .etw-block-head h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 3.4vw, 40px);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 12px;
            color: var(--ink);
        }

        .etw-block-head p {
            font-size: 16px;
            color: var(--muted);
        }

        .etw-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            position: relative;
        }

        .etw-steps::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 12%;
            right: 12%;
            height: 2px;
            background: repeating-linear-gradient(to right, var(--primary-light) 0 8px, transparent 8px 16px);
            z-index: 0;
        }

        .etw-step {
            background: white;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 28px;
            position: relative;
            z-index: 1;
            transition: transform .25s, box-shadow .25s;
        }

        .etw-step:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -28px rgba(29, 78, 216, 0.25);
        }

        .etw-step-num {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            box-shadow: 0 8px 18px -6px rgba(29, 78, 216, 0.55);
        }

        .etw-step h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--ink);
        }

        .etw-step p {
            font-size: 14px;
            color: var(--muted);
            margin: 0;
        }

        .etw-step-icon {
            font-size: 22px;
            color: var(--primary);
            margin-top: 18px;
            display: block;
        }

        .etw-impact {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: white;
            padding: 80px 24px;
            margin: 60px 0;
        }

        .etw-impact .etw-block-head h2 {
            color: white;
        }

        .etw-impact .etw-block-head p {
            color: rgba(255, 255, 255, 0.7);
        }

        .etw-impact .etw-tag {
            background: rgba(255, 255, 255, 0.12);
            color: white;
        }

        .etw-impact-grid {
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .etw-impact-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 28px;
            text-align: center;
            backdrop-filter: blur(8px);
            transition: transform .25s, background .25s;
        }

        .etw-impact-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.1);
        }

        .etw-impact-card i {
            font-size: 28px;
            color: var(--accent);
            margin-bottom: 14px;
        }

        .etw-impact-card .v {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .etw-impact-card .k {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 10px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .etw-tgrid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .etw-tcard {
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border-soft);
            padding: 28px;
            position: relative;
            transition: transform .25s, box-shadow .25s;
        }

        .etw-tcard:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -28px rgba(29, 78, 216, 0.25);
        }

        .etw-tcard .q {
            position: absolute;
            top: 18px;
            right: 22px;
            font-size: 42px;
            color: var(--primary-light);
            font-family: 'Playfair Display', serif;
            line-height: 1;
        }

        .etw-tcard p {
            font-size: 14px;
            color: var(--ink-soft);
            line-height: 1.7;
            margin: 0 0 22px;
            position: relative;
            z-index: 1;
        }

        .etw-tauthor {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .etw-tavatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            border: 2px solid white;
            box-shadow: 0 6px 14px -6px rgba(15, 23, 42, 0.2);
        }

        .etw-tauthor h5 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
            color: var(--ink);
        }

        .etw-tauthor span {
            font-size: 12px;
            color: var(--muted);
        }

        .etw-faq {
            max-width: 820px;
            margin: 0 auto;
        }

        .etw-faq-item {
            background: white;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: border-color .2s;
        }

        .etw-faq-item.open {
            border-color: var(--primary);
        }

        .etw-faq-q {
            width: 100%;
            padding: 20px 24px;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            font-family: inherit;
        }

        .etw-faq-q i {
            transition: transform .25s;
            color: var(--primary);
        }

        .etw-faq-item.open .etw-faq-q i {
            transform: rotate(180deg);
        }

        .etw-faq-a {
            max-height: 0;
            padding: 0 24px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            overflow: hidden;
            transition: max-height .3s ease, padding .3s ease;
        }

        .etw-faq-item.open .etw-faq-a {
            max-height: 240px;
            padding: 0 24px 22px;
        }

        .etw-donate {
            margin: 60px auto;
            max-width: 1180px;
            background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
            border-radius: 28px;
            padding: 56px 48px;
            color: white;
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .etw-donate::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12), transparent 65%);
        }

        .etw-donate::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.18), transparent 65%);
        }

        .etw-donate__inner {
            position: relative;
            z-index: 2;
        }

        .etw-donate h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 3.2vw, 38px);
            font-weight: 800;
            margin-bottom: 14px;
            line-height: 1.15;
            color: white;
        }

        .etw-donate p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 22px;
            max-width: 480px;
        }

        .etw-donate-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .etw-donate .etw-btn-primary {
            background: white;
            color: var(--primary);
            box-shadow: 0 10px 28px -8px rgba(0, 0, 0, 0.3);
        }

        .etw-donate .etw-btn-ghost {
            color: white;
            border-color: rgba(255, 255, 255, 0.4);
            background: transparent;
        }

        .etw-donate .etw-btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            color: white;
        }

        .etw-amts {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .etw-amt {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 14px 16px;
            text-align: center;
            cursor: pointer;
            transition: background .2s, transform .2s;
            color: white;
            font-family: inherit;
        }

        .etw-amt:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .etw-amt .a {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 800;
        }

        .etw-amt .b {
            font-size: 11px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .etw-reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .8s ease, transform .8s ease;
        }

        .etw-reveal.in {
            opacity: 1;
            transform: none;
        }

        @media (max-width: 1200px) {
            .etw-circle {
                width: 110px;
                height: 110px;
            }

            .etw-circle.c1,
            .etw-circle.c3 {
                left: 150px;
            }

            .etw-circle.c2 {
                left: 100px;
            }

            .etw-circle.c4,
            .etw-circle.c6 {
                right: 150px;
            }

            .etw-circle.c5 {
                right: 100px;
            }
        }

        @media (max-width: 1024px) {

            .etw-circle,
            .etw-arc {
                display: none;
            }

            .etw-stage {
                padding: 24px 18px 36px;
            }

            .etw-stage__inner {
                min-height: auto;
            }

            .etw-trust {
                grid-template-columns: repeat(2, 1fr);
            }

            .etw-steps,
            .etw-tgrid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .etw-steps::before {
                display: none;
            }

            .etw-impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .etw-donate {
                grid-template-columns: 1fr;
                padding: 36px 28px;
            }
        }

        @media (max-width: 540px) {
            .etw-stats {
                gap: 16px;
            }

            .etw-stat .n {
                font-size: 32px;
            }

            .etw-trust,
            .etw-impact-grid {
                grid-template-columns: 1fr;
            }

            .etw-progress-counter .big {
                font-size: 38px;
            }

            .etw-amts {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ===== Crisis Story Modal ===== */
        .etw-circle {
            cursor: pointer;
        }

        .etw-circle:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 4px;
            border-radius: 50%;
        }

        .etw-story-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }

        .etw-story-modal.open {
            opacity: 1;
            pointer-events: auto;
        }

        .etw-story-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .etw-story-dialog {
            position: relative;
            background: white;
            border-radius: 20px;
            max-width: 560px;
            width: 100%;
            max-height: calc(100vh - 48px);
            overflow: hidden;
            box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.5);
            transform: scale(0.92);
            transition: transform .3s cubic-bezier(.22, .61, .36, 1);
            display: flex !important;
            flex-direction: column !important;
        }

        .etw-story-modal.open .etw-story-dialog {
            transform: scale(1);
        }

        .etw-story-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.65);
            color: white !important;
            border: none;
            cursor: pointer;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s, transform .2s;
            font-size: 14px;
            padding: 0;
        }

        .etw-story-close:hover {
            background: rgba(15, 23, 42, 0.9);
            transform: rotate(90deg);
        }

        .etw-story-media {
            position: relative;
            width: 100%;
            height: 280px;
            min-height: 280px;
            flex-shrink: 0;
            background: #0f172a;
            overflow: hidden;
        }

        .etw-story-slide {
            position: absolute;
            inset: 0;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            opacity: 0;
            transition: opacity 1s ease;
            animation: etw-storyZoom 6s ease-in-out infinite alternate;
        }

        .etw-story-slide.active {
            opacity: 1;
        }

        @keyframes etw-storyZoom {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.08);
            }
        }

        .etw-story-media-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, transparent 40%, transparent 60%, rgba(15, 23, 42, 0.6) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 16px 18px;
            z-index: 3;
        }

        .etw-story-badge {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.95);
            color: white;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            padding: 4px 10px;
            border-radius: 999px;
            text-transform: uppercase;
        }

        .etw-story-live-dot {
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            animation: etw-pulse 1s infinite;
        }

        .etw-story-pips {
            align-self: center;
            display: flex;
            gap: 6px;
        }

        .etw-story-pips .pip {
            width: 30px;
            height: 3px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 999px;
            transition: background .3s;
        }

        .etw-story-pips .pip.active {
            background: white;
        }

        .etw-story-body {
            padding: 20px 24px 24px;
            overflow-y: auto;
            flex: 1;
        }

        .etw-story-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .etw-story-cat {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--primary-tint);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 11px;
        }

        .etw-story-loc {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .etw-story-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--ink);
            margin: 0 0 10px;
            letter-spacing: -0.01em;
        }

        .etw-story-lead {
            font-size: 13px;
            line-height: 1.6;
            color: var(--ink-soft);
            margin: 0 0 16px;
        }

        .etw-story-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 12px;
            background: var(--primary-tint);
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .etw-story-stat {
            text-align: center;
        }

        .etw-story-stat .v {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .etw-story-stat .k {
            font-size: 10px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 600;
        }

        .etw-story-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .etw-story-btn {
            display: inline-flex !important;
            align-items: center;
            gap: 8px;
            padding: 11px 22px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            font-weight: 700;
            transition: transform .2s, box-shadow .2s, background .2s, color .2s;
            text-decoration: none !important;
            cursor: pointer;
            border: 2px solid transparent;
            justify-content: center;
        }

        .etw-story-btn-primary {
            background: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 6px 16px -6px rgba(29, 78, 216, 0.5);
        }

        .etw-story-btn-primary:hover {
            background: #1e3a8a !important;
            color: #ffffff !important;
            transform: translateY(-2px);
        }

        .etw-story-btn-ghost {
            background: white !important;
            color: var(--ink) !important;
            border-color: var(--border) !important;
        }

        .etw-story-btn-ghost:hover {
            color: var(--primary) !important;
            border-color: var(--primary) !important;
            transform: translateY(-2px);
        }

        @media (max-width: 540px) {
            .etw-story-media {
                height: 220px;
                min-height: 220px;
            }

            .etw-story-title {
                font-size: 18px;
            }

            .etw-story-stat .v {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="etw">

        {{-- HERO --}}
        <section class="etw-stage" id="hero">
            <div class="etw-stage__inner">
                <svg class="etw-arc" viewBox="0 0 1280 720" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M 180 100 Q 640 240, 1100 100" />
                    <path d="M 60 320  Q 640 500, 1220 320" />
                    <path d="M 180 540 Q 640 700, 1100 540" />
                </svg>

                <div class="etw-circle l c1" data-modal="flood" role="button" tabindex="0"><span class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1547683905-f686c993aae5?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-water"></i>Flood</span>
                </div>
                <div class="etw-circle l c2" data-modal="medical" role="button" tabindex="0"><span class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1551601651-2a8555f1a136?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-heart-pulse"></i>Medical</span>
                </div>
                <div class="etw-circle l c3" data-modal="firehouse" role="button" tabindex="0"><span
                        class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-fire"></i>House Fire</span>
                </div>
                <div class="etw-circle r c4" data-modal="accident" role="button" tabindex="0"><span
                        class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1568438350562-2cae6d394ad0?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-car-burst"></i>Accident</span>
                </div>
                <div class="etw-circle r c5" data-modal="student" role="button" tabindex="0"><span class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-graduation-cap"></i>Student Support</span>
                </div>
                <div class="etw-circle r c6" data-modal="legacy" role="button" tabindex="0"><span class="ring"></span>
                    <div class="photo"
                        style="background-image:url('https://images.unsplash.com/photo-1579208030886-b937da0925dc?w=300&q=70')">
                    </div><span class="chip"><i class="fas fa-envelope"></i>Last Message</span>
                </div>

                <div class="etw-headline">
                    <h1>When crisis strikes,<br><em>support is already here</em></h1>
                    <p class="lead">e-Tawassul is IIUM's comprehensive platform for student crisis management and digital
                        legacy preservation — ensuring no student faces hardship alone.</p>
                        <div class="etw-clock"><span class="dot"></span> Live · <span id="etwTime">--:--:--</span> · <span
                        id="etwDate">--</span></div>
                </div>
                @php
                    $helped = $totalHelped ?? 2487;
                    $helpedFormatted = $helped >= 1000 ? round($helped / 1000, 1) . 'k' : (string) $helped;
                @endphp
                {{-- <div class="etw-stats">
                    <div class="etw-stat">
                        <div class="n">24</div>
                        <div class="l">Hours</div>
                    </div>
                    <div class="etw-stat-sep"></div>
                    <div class="etw-stat">
                        <div class="n">7</div>
                        <div class="l">Days</div>
                    </div>
                    <div class="etw-stat-sep"></div>
                    <div class="etw-stat">
                        <div class="n">{{ $casesSupported ?? 12 }}</div>
                        <div class="l">Active Cases</div>
                    </div>
                    <div class="etw-stat-sep"></div>
                    <div class="etw-stat">
                        <div class="n">{{ $helpedFormatted }}</div>
                        <div class="l">Students Helped</div>
                    </div>
                </div> --}}

                <div class="etw-progress-wrap">
                    <h2>Support Progress</h2>
                    <div class="etw-progress-card">
                        <div class="etw-progress-top">
                            <div class="etw-progress-counter">
                                <span class="big" id="etwCount">0</span>
                                <span class="of">/ {{ $capacityGoal }}</span>
                            </div>
                            <span class="etw-progress-pct" id="etwPct">0%</span>
                        </div>
                        <div class="etw-progress-bar">
                            <div class="etw-progress-fill" id="etwFill" data-target="{{ $progressPct }}"></div>
                        </div>
                        <div class="etw-progress-meta">
                            <span>Cases Supported This Year</span>
                            <span>Capacity Goal: <strong>{{ $capacityGoal }} cases</strong></span>
                        </div>
                        <div class="etw-progress-row">
                            <div class="etw-progress-cell">
                                <div class="v success">RM <span id="etwRaised">0</span></div>
                                <div class="k">Total Raised</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="etw-cta-row">
                    <a href="#cases" class="etw-btn etw-btn-solid"><i class="fas fa-bullhorn"></i> See Active Cases</a>
                </div>
            </div>
        </section>

        {{-- TRUST BAR (own section, just below the hero) --}}
        <section class="etw-trust-section">
            <div class="etw-trust">
                <div class="etw-trust-item">
                    <div class="etw-trust-icon"><i class="fas fa-lock"></i></div>
                    <div>
                        <h4>Privacy by Design</h4>
                        <p>Your data is protected</p>
                    </div>
                </div>
                <div class="etw-trust-item">
                    <div class="etw-trust-icon"><i class="fas fa-link"></i></div>
                    <div>
                        <h4>Blockchain Secured</h4>
                        <p>Tamper-proof &amp; verifiable</p>
                    </div>
                </div>
                <div class="etw-trust-item">
                    <div class="etw-trust-icon"><i class="fas fa-bell"></i></div>
                    <div>
                        <h4>Real-time Alerts</h4>
                        <p>Instant crisis notifications</p>
                    </div>
                </div>
                <div class="etw-trust-item">
                    <div class="etw-trust-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div>
                        <h4>Education First</h4>
                        <p>Academic continuity assured</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ACTIVE CASES (uses controller data + your existing x-crisis-card component) --}}
        <section class="etw-block" id="cases">
            <div class="etw-block-head etw-reveal">
                <span class="etw-tag">Active Cases</span>
                <h2>Cases that need support right now</h2>
                <p>Every case below has been verified by IIUM administrators.</p>
            </div>

            @if ($activeCrises->isEmpty())
                <div class="text-center">
                    <i class="bi bi-emoji-smile" style="font-size: 48px; color: var(--success);"></i>
                    <h5 class="mt-3">No active cases at this time</h5>
                    <p class="text-muted">Alhamdulillah, there are no active crisis cases requiring support right now.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($activeCrises as $crisis)
                        <div class="col-md-6 col-lg-4"><x-crisis-card :crisis="$crisis" /></div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- LIVE STATS DASHBOARD --}}
        <section class="etw-impact" id="impact">
            <div class="etw-block-head etw-reveal">
                <span class="etw-tag">Our Impact</span>
                <h2>Real numbers, real lives changed</h2>
                <p>Every figure below represents a student supported through our platform.</p>
            </div>
            <div class="etw-impact-grid">
                <div class="etw-impact-card etw-reveal"><i class="fas fa-users"></i>
                    <div class="v" data-count="{{ $totalHelped }}">0</div>
                    <div class="k">Students Helped</div>
                </div>
                <div class="etw-impact-card etw-reveal"><i class="fas fa-hand-holding-dollar"></i>
                    <div class="v">RM <span data-count="{{ $totalRaised }}">0</span></div>
                    <div class="k">Total Donations</div>
                </div>
                <div class="etw-impact-card etw-reveal"><i class="fas fa-heart"></i>
                    <div class="v" data-count="{{ $totalSupporters }}">0</div>
                    <div class="k">Supporters</div>
                </div>
                <div class="etw-impact-card etw-reveal"><i class="fas fa-shield-halved"></i>
                    <div class="v" data-count="{{ $legacyMessages }}">0</div>
                    <div class="k">Legacy Messages</div>
                </div>
            </div>
        </section>

        {{-- TESTIMONIALS --}}
        <section class="etw-block" id="testimonials">
            <div class="etw-block-head etw-reveal">
                <span class="etw-tag">Voices</span>
                <h2>Stories from students we've supported</h2>
                <p>Real testimonials from members of the IIUM community whose lives were changed by e-Tawassul.</p>
            </div>
            <div class="etw-tgrid">
                <div class="etw-tcard etw-reveal"><span class="q">"</span>
                    <p>When my father passed during my final semester, I thought I'd have to drop out. e-Tawassul connected
                        me with support I didn't know existed. I graduated last month, Alhamdulillah.</p>
                    <div class="etw-tauthor">
                        <div class="etw-tavatar"
                            style="background-image:url('https://images.unsplash.com/photo-1633332755192-727a05c4013d?w=200&q=80')">
                        </div>
                        <div>
                            <h5>Aiman Hakim</h5><span>Engineering, Class of 2024</span>
                        </div>
                    </div>
                </div>
                <div class="etw-tcard etw-reveal"><span class="q">"</span>
                    <p>The blockchain verification is what made me trust this platform. I could see exactly where my
                        donation went and the impact it made. Real transparency in giving.</p>
                    <div class="etw-tauthor">
                        <div class="etw-tavatar"
                            style="background-image:url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=200&q=80')">
                        </div>
                        <div>
                            <h5>Dr. Siti Nurhaliza</h5><span>Donor &amp; IIUM Alumni</span>
                        </div>
                    </div>
                </div>
                <div class="etw-tcard etw-reveal"><span class="q">"</span>
                    <p>My family was devastated by the December floods. Within 48 hours of submitting our case, we received
                        emergency aid. The speed of response saved my brother's semester.</p>
                    <div class="etw-tauthor">
                        <div class="etw-tavatar"
                            style="background-image:url('https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&q=80')">
                        </div>
                        <div>
                            <h5>Nur Aishah</h5><span>Medicine, Year 4</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="etw-block" id="faq">
            <div class="etw-block-head etw-reveal">
                <span class="etw-tag">Common Questions</span>
                <h2>Frequently asked questions</h2>
                <p>Everything you need to know about how e-Tawassul protects students and donors alike.</p>
            </div>
            <div class="etw-faq">
                <div class="etw-faq-item"><button type="button" class="etw-faq-q">How does e-Tawassul verify cases? <i
                            class="fas fa-chevron-down"></i></button>
                    <div class="etw-faq-a">Every case is reviewed by IIUM administrators who verify identity documents,
                        supporting evidence (hospital records, police reports, etc.), and the student's enrollment status.
                        Only verified cases are made public.</div>
                </div>
                <div class="etw-faq-item"><button type="button" class="etw-faq-q">What does "blockchain-verified" mean?
                        <i class="fas fa-chevron-down"></i></button>
                    <div class="etw-faq-a">Every donation, verification, and disbursement is recorded on a permissioned
                        audit chain. Once a transaction is logged, it cannot be altered or deleted — providing
                        tamper-evident proof of every action.</div>
                </div>
                <div class="etw-faq-item"><button type="button" class="etw-faq-q">Who can submit a crisis report? <i
                            class="fas fa-chevron-down"></i></button>
                    <div class="etw-faq-a">Currently enrolled IIUM students, their registered next-of-kin, and authorised
                        university representatives can submit crisis reports. All submissions go through the same
                        verification process.</div>
                </div>
                <div class="etw-faq-item"><button type="button" class="etw-faq-q">How are legacy messages kept private?
                        <i class="fas fa-chevron-down"></i></button>
                    <div class="etw-faq-a">Legacy messages are end-to-end encrypted with keys held only by the author.
                        Messages are only released to verified next-of-kin after specific conditions defined by the student
                        are met.</div>
                </div>
                <div class="etw-faq-item"><button type="button" class="etw-faq-q">Is there any fee for using e-Tawassul?
                        <i class="fas fa-chevron-down"></i></button>
                    <div class="etw-faq-a">No. e-Tawassul is a non-profit initiative by IIUM. There are no platform fees,
                        no processing fees, and no hidden costs. 100% of every donation reaches the verified student.</div>
                </div>
            </div>
        </section>

        {{-- HOW IT WORKS (moved to bottom) --}}
        <section class="etw-block" id="how">
            <div class="etw-block-head etw-reveal">
                <span class="etw-tag">How It Works</span>
                <h2>Three simple steps to get the support you need</h2>
                <p>From the moment a crisis is reported to verified support reaching the student, every step is transparent.
                </p>
            </div>
            <div class="etw-steps">
                <div class="etw-step etw-reveal">
                    <div class="etw-step-num">1</div>
                    <h3>Report the crisis</h3>
                    <p>Students or next-of-kin submit a case through a secure form. All evidence is encrypted at rest.</p><i
                        class="fas fa-file-shield etw-step-icon"></i>
                </div>
                <div class="etw-step etw-reveal">
                    <div class="etw-step-num">2</div>
                    <h3>Admin verification</h3>
                    <p>IIUM administrators review the case, validate documents, and publish it once confirmed authentic.</p>
                    <i class="fas fa-user-check etw-step-icon"></i>
                </div>
                <div class="etw-step etw-reveal">
                    <div class="etw-step-num">3</div>
                    <h3>Community support</h3>
                    <p>Donations are tracked on-chain. Every contribution and disbursement is permanently recorded.</p><i
                        class="fas fa-handshake-angle etw-step-icon"></i>
                </div>
            </div>
        </section>

        {{-- CRISIS STORY MODAL --}}
        <div class="etw-story-modal" id="etwStoryModal" aria-hidden="true">
            <div class="etw-story-backdrop" data-close></div>
            <div class="etw-story-dialog" role="dialog" aria-modal="true" aria-labelledby="etwStoryTitle">
                <button class="etw-story-close" data-close aria-label="Close"><i class="fas fa-times"></i></button>
                <div class="etw-story-media">
                    <div class="etw-story-slide" data-idx="0"></div>
                    <div class="etw-story-slide" data-idx="1"></div>
                    <div class="etw-story-slide" data-idx="2"></div>
                    <div class="etw-story-media-overlay">
                        <span class="etw-story-badge"><span class="etw-story-live-dot"></span> LIVE STORY</span>
                        <span class="etw-story-pips">
                            <span class="pip active"></span><span class="pip"></span><span class="pip"></span>
                        </span>
                    </div>
                </div>
                <div class="etw-story-body">
                    <div class="etw-story-meta">
                        <span class="etw-story-cat" id="etwStoryCat"><i class="fas fa-water"></i> Flood</span>
                        <span class="etw-story-loc"><i class="fas fa-location-dot"></i> IIUM</span>
                    </div>
                    <h3 class="etw-story-title" id="etwStoryTitle">—</h3>
                    <p class="etw-story-lead" id="etwStoryLead">—</p>
                    <div class="etw-story-actions">
                        <a href="#cases" class="etw-story-btn etw-story-btn-primary" data-close><i
                                class="fas fa-bullhorn"></i> See How We Help</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            function animateNumber(el, to, duration, formatter) {
                if (!el) return;
                formatter = formatter || ((n) => Math.floor(n));
                const start = performance.now();

                function frame(now) {
                    const t = Math.min(1, (now - start) / duration);
                    const eased = 1 - Math.pow(1 - t, 3);
                    el.textContent = formatter(eased * to);
                    if (t < 1) requestAnimationFrame(frame);
                    else el.textContent = formatter(to);
                }
                requestAnimationFrame(frame);
            }

            // Live clock
            (function tickClock() {
                const t = document.getElementById('etwTime');
                const d = document.getElementById('etwDate');

                function pad(n) {
                    return n < 10 ? '0' + n : n;
                }

                function update() {
                    const now = new Date();
                    if (t) t.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now
                        .getSeconds());
                    if (d) d.textContent = now.toLocaleDateString('en-MY', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'short'
                    });
                }
                update();
                setInterval(update, 1000);
            })();

            // Hero Support Progress
            const fill = document.getElementById('etwFill');
            if (fill) {
                const target = parseFloat(fill.dataset.target || '0');
                const cases = {{ (int) $casesSupported }};
                const raised = {{ (int) $totalRaised }};

                const io = new IntersectionObserver((entries) => {
                    entries.forEach(e => {
                        if (e.isIntersecting) {
                            fill.style.width = target + '%';
                            animateNumber(document.getElementById('etwCount'), cases, 1600);
                            animateNumber(document.getElementById('etwPct'), target, 1600, (n) => Math
                                .round(n) + '%');
                            animateNumber(document.getElementById('etwRaised'), raised, 1800, (n) =>
                                Math.floor(n).toLocaleString());
                            io.disconnect();
                        }
                    });
                }, {
                    threshold: 0.2
                });
                io.observe(fill);
            }

            // Scroll reveal + impact card count-up
            const els = document.querySelectorAll('.etw-reveal');
            const rio = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        e.target.querySelectorAll('[data-count]').forEach(el => {
                            if (el.dataset.done) return;
                            const to = parseFloat(el.dataset.count);
                            animateNumber(el, to, 2000, (n) => Math.floor(n).toLocaleString());
                            el.dataset.done = '1';
                        });
                        rio.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.15
            });
            els.forEach(el => rio.observe(el));

            // FAQ accordion
            document.querySelectorAll('.etw-faq-q').forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = btn.parentElement;
                    const wasOpen = item.classList.contains('open');
                    document.querySelectorAll('.etw-faq-item').forEach(i => i.classList.remove('open'));
                    if (!wasOpen) item.classList.add('open');
                });
            });

            // Donate pills feedback
            document.querySelectorAll('.etw-amt').forEach(p => {
                p.addEventListener('click', () => {
                    document.querySelectorAll('.etw-amt').forEach(o => o.style.background = '');
                    p.style.background = 'rgba(255,255,255,0.3)';
                });
            });

            // Gentle hero parallax
            const circles = document.querySelectorAll('.etw-circle');
            window.addEventListener('scroll', () => {
                const y = window.scrollY;
                if (y > 600) return;
                circles.forEach((c, i) => {
                    const factor = (i % 2 === 0 ? 1 : -1) * 0.05;
                    c.style.translate = `0 ${y * factor}px`;
                });
            }, {
                passive: true
            });
        })();

        // ============= CRISIS STORY MODAL =============
        const ETW_STORIES = {
            flood: {
                cat: '<i class="fas fa-water"></i> Flood',
                loc: 'IIUM Gombak · Mahallah Affected',
                title: 'Monsoon floods displace 47 IIUM students from off-campus housing',
                lead: 'Heavy rainfall in Gombak left several student rentals submerged. e-Tawassul activated emergency relocation, providing temporary mahallah housing, replacement essentials, and academic deferment letters within 6 hours of verification.',
                slides: [
                    'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=900&q=80',
                    'https://images.unsplash.com/photo-1583244532610-2a234c44d2c6?w=900&q=80',
                    'https://images.unsplash.com/photo-1574788175366-15db8e0d0915?w=900&q=80'
                ],
                stats: [{
                    v: '47',
                    k: 'Students relocated'
                }, {
                    v: '6h',
                    k: 'Response time'
                }, {
                    v: 'RM 28k',
                    k: 'Aid disbursed'
                }]
            },
            medical: {
                cat: '<i class="fas fa-heart-pulse"></i> Medical',
                loc: 'IIUMMC Kuantan · Critical Care',
                title: 'Final-year student\'s urgent surgery funded in 48 hours',
                lead: 'A Kulliyyah of Engineering student needed emergency cardiac surgery beyond family means. e-Tawassul verified the case with IIUMMC, opened a transparent fund, and donors fully covered RM 42,300 within 48 hours — surgery proceeded on time.',
                slides: [
                    'https://images.unsplash.com/photo-1551601651-2a8555f1a136?w=900&q=80',
                    'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=900&q=80',
                    'https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=900&q=80'
                ],
                stats: [{
                    v: '48h',
                    k: 'Funded'
                }, {
                    v: '100%',
                    k: 'Bills covered'
                }, {
                    v: '312',
                    k: 'Donors joined'
                }]
            },
            firehouse: {
                cat: '<i class="fas fa-fire"></i> House Fire',
                loc: 'Family home · Kelantan',
                title: 'Student family loses home — academic continuity preserved',
                lead: 'A house fire destroyed the family home of a Kulliyyah of Laws student, including textbooks, devices, and personal documents. e-Tawassul coordinated with the Mahallah office for a replacement laptop, free textbook loans, and replacement-document support so finals could proceed without delay.',
                slides: [
                    'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=900&q=80',
                    'https://images.unsplash.com/photo-1574870111867-089730e5a72b?w=900&q=80',
                    'https://images.unsplash.com/photo-1583936232743-1be91040548d?w=900&q=80'
                ],
                stats: [{
                    v: '1',
                    k: 'Family helped'
                }, {
                    v: 'RM 15k',
                    k: 'Replacement aid'
                }, {
                    v: '0',
                    k: 'Exams missed'
                }]
            },
            accident: {
                cat: '<i class="fas fa-car-burst"></i> Accident',
                loc: 'Karak Highway · Critical',
                title: 'Motorcycle accident leaves student in ICU — family supported',
                lead: 'A second-year student commuting from Gombak was severely injured. e-Tawassul activated next-of-kin protocols within the hour, arranged hospital liaison with IIUMMC, transport for family from Terengganu, and an academic hold so credits weren\'t lost during recovery.',
                slides: [
                    'https://images.unsplash.com/photo-1568438350562-2cae6d394ad0?w=900&q=80',
                    'https://images.unsplash.com/photo-1612831455540-fcbe1f7e3739?w=900&q=80',
                    'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=900&q=80'
                ],
                stats: [{
                    v: '<1h',
                    k: 'NOK notified'
                }, {
                    v: 'RM 18k',
                    k: 'Family aid'
                }, {
                    v: '1 sem',
                    k: 'Academic hold'
                }]
            },
            student: {
                cat: '<i class="fas fa-graduation-cap"></i> Student Support',
                loc: 'IIUM-wide · Welfare Programs',
                title: '612 students receiving ongoing financial & wellbeing support',
                lead: 'Beyond emergencies, e-Tawassul runs continuous welfare programs: monthly food assistance, mental-health connections through CHARIS, peer-mentoring, and emergency stipends for students whose families lost income — keeping degrees on track.',
                slides: [
                    'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=900&q=80',
                    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=900&q=80',
                    'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=900&q=80'
                ],
                stats: [{
                    v: '612',
                    k: 'Under support'
                }, {
                    v: '14',
                    k: 'Kulliyyahs'
                }, {
                    v: 'RM 184k',
                    k: 'Distributed'
                }]
            },
            legacy: {
                cat: '<i class="fas fa-envelope"></i> Last Message',
                loc: 'Digital Legacy · Encrypted Vault',
                title: 'Digital legacy: words that reach loved ones, when it matters most',
                lead: 'e-Tawassul preserves voice notes, letters, and final wishes that students choose to share with their next-of-kin under specific conditions. End-to-end encrypted, blockchain-verified, released only when verified — giving every student peace of mind.',
                slides: [
                    'https://images.unsplash.com/photo-1579208030886-b937da0925dc?w=900&q=80',
                    'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=900&q=80',
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=900&q=80'
                ],
                stats: [{
                    v: '892',
                    k: 'Messages stored'
                }, {
                    v: 'AES-256',
                    k: 'Encryption'
                }, {
                    v: '100%',
                    k: 'Private'
                }]
            }
        };

        (function() {
            const modal = document.getElementById('etwStoryModal');
            if (!modal) return;
            const slides = modal.querySelectorAll('.etw-story-slide');
            const pips = modal.querySelectorAll('.etw-story-pips .pip');
            const catEl = document.getElementById('etwStoryCat');
            const titleEl = document.getElementById('etwStoryTitle');
            const leadEl = document.getElementById('etwStoryLead');
            let timer = null,
                idx = 0;

            function show(i) {
                slides.forEach((s, j) => s.classList.toggle('active', j === i));
                pips.forEach((p, j) => p.classList.toggle('active', j === i));
            }

            function open(key) {
                const st = ETW_STORIES[key];
                if (!st) return;
                catEl.innerHTML = st.cat;
                titleEl.textContent = st.title;
                leadEl.textContent = st.lead;
                slides.forEach((s, i) => s.style.backgroundImage = `url('${st.slides[i]}')`);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                idx = 0;
                show(0);
                timer = setInterval(() => {
                    idx = (idx + 1) % 3;
                    show(idx);
                }, 2500);
            }

            function close() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }
            document.querySelectorAll('.etw-circle[data-modal]').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    open(el.dataset.modal);
                });
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        open(el.dataset.modal);
                    }
                });
            });
            modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', close));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('open')) close();
            });
        })();
    </script>
@endpush
