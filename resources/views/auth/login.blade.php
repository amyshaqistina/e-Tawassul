<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - e-Tawassul</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100vh; width: 100vw; overflow: hidden; }
        [x-cloak] { display: none !important; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            position: relative;
            color: #0f172a;
            background:
                radial-gradient(circle at 18% 28%, rgba(163,217,255,0.45) 0%, transparent 50%),
                radial-gradient(circle at 82% 72%, rgba(195,210,255,0.4) 0%, transparent 55%),
                linear-gradient(135deg, #eaf4ff 0%, #dceeff 50%, #cfe6ff 100%);
        }

        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(40px);
            opacity: 0.4;
        }
        body::before {
            width: 380px; height: 380px;
            top: -120px; right: -100px;
            background: radial-gradient(circle, #a3d8ff 0%, transparent 70%);
            animation: bgFloat 14s ease-in-out infinite;
        }
        body::after {
            width: 320px; height: 320px;
            bottom: -100px; left: -80px;
            background: radial-gradient(circle, #c8b8ff 0%, transparent 70%);
            animation: bgFloat 16s ease-in-out infinite reverse;
        }
        @keyframes bgFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            50%     { transform: translate(30px,-25px) scale(1.08); }
        }

        .login-shell {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: calc(100vh - 28px);
            max-height: 660px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(20, 60, 120, 0.18);
            position: relative;
            z-index: 10;
        }

        /* ============ LEFT SCENE PANEL ============ */
        .scene-panel {
            flex: 1.15;
            position: relative;
            overflow: hidden;
            min-width: 0;
            transition: background 1.5s ease;
        }

        /* Each time has its own sky gradient */
        .scene-panel[data-time="dawn"]   { background: linear-gradient(180deg, #fde68a 0%, #fbb985 22%, #f4a3b8 50%, #c8d4f0 80%, #a8c8e8 100%); }
        .scene-panel[data-time="noon"]   { background: linear-gradient(180deg, #b6e0fe 0%, #87c8f5 35%, #5fa9d8 70%, #4a8fc4 100%); }
        .scene-panel[data-time="sunset"] { background: linear-gradient(180deg, #ffae73 0%, #ff7e5f 25%, #c44569 55%, #6b3fa0 85%, #3a2654 100%); }
        .scene-panel[data-time="night"]  { background: linear-gradient(180deg, #0a1f3d 0%, #1a3b6e 45%, #2d5a9e 80%, #3a6a9e 100%); }

        .scene-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Hide time-specific elements unless this time is active.
           Using visibility (not display) for maximum SVG compatibility. */
        .only-dawn,
        .only-noon,
        .only-sunset,
        .only-night { visibility: hidden; }
        .scene-panel[data-time="dawn"]   .only-dawn,
        .scene-panel[data-time="dawn"]   .only-dawn   * { visibility: visible; }
        .scene-panel[data-time="noon"]   .only-noon,
        .scene-panel[data-time="noon"]   .only-noon   * { visibility: visible; }
        .scene-panel[data-time="sunset"] .only-sunset,
        .scene-panel[data-time="sunset"] .only-sunset * { visibility: visible; }
        .scene-panel[data-time="night"]  .only-night,
        .scene-panel[data-time="night"]  .only-night  * { visibility: visible; }

        /* Logo + time badge overlay */
        .scene-overlay-top {
            position: absolute;
            top: 22px;
            left: 22px;
            right: 22px;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .brand-block { display: flex; align-items: center; gap: 10px; }
        .brand-logo {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 21px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.45);
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            transition: all 0.6s ease;
        }
        .scene-panel[data-time="dawn"]   .brand-logo { background: rgba(255,255,255,0.45); color: #d85a30; }
        .scene-panel[data-time="noon"]   .brand-logo { background: rgba(255,255,255,0.55); color: #0c4a7c; }
        .scene-panel[data-time="sunset"] .brand-logo { background: rgba(255,255,255,0.3);  color: #c2185b; }
        .scene-panel[data-time="night"]  .brand-logo { background: rgba(255,255,255,0.18); color: #ffd66e; }

        .brand-title {
            font-size: 16px; font-weight: 700; letter-spacing: 0.2px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.12);
            transition: color 0.6s ease;
            line-height: 1.1;
        }
        .brand-sub {
            font-size: 10.5px; font-weight: 500;
            transition: color 0.6s ease;
            line-height: 1.2;
        }
        .scene-panel[data-time="dawn"]   .brand-title,
        .scene-panel[data-time="dawn"]   .brand-sub { color: #1a2d4a; }
        .scene-panel[data-time="noon"]   .brand-title,
        .scene-panel[data-time="noon"]   .brand-sub { color: #0a1f3d; }
        .scene-panel[data-time="sunset"] .brand-title,
        .scene-panel[data-time="sunset"] .brand-sub { color: #fff; }
        .scene-panel[data-time="night"]  .brand-title,
        .scene-panel[data-time="night"]  .brand-sub { color: #fff; }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        .scene-panel[data-time="dawn"]   .time-badge { background: rgba(255,255,255,0.5);  color: #d85a30; }
        .scene-panel[data-time="noon"]   .time-badge { background: rgba(255,255,255,0.55); color: #0c4a7c; }
        .scene-panel[data-time="sunset"] .time-badge { background: rgba(255,255,255,0.25); color: #fff; }
        .scene-panel[data-time="night"]  .time-badge { background: rgba(255,255,255,0.15); color: #ffd66e; }
        .time-badge:hover { transform: scale(1.05); }

        /* Tagline */
        .scene-tagline {
            position: absolute;
            bottom: 26px;
            left: 24px;
            right: 24px;
            z-index: 5;
        }
        .scene-tagline .line1, .scene-tagline .line2 {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.3px;
            opacity: 0;
            text-shadow: 0 2px 14px rgba(0,0,0,0.3);
            transition: color 0.6s ease;
        }
        .scene-panel[data-time="dawn"]   .scene-tagline .line1 { color: #fff; }
        .scene-panel[data-time="dawn"]   .scene-tagline .line2 { color: #fff8d6; }
        .scene-panel[data-time="noon"]   .scene-tagline .line1 { color: #fff; }
        .scene-panel[data-time="noon"]   .scene-tagline .line2 { color: #fff8d6; }
        .scene-panel[data-time="sunset"] .scene-tagline .line1 { color: #fff; }
        .scene-panel[data-time="sunset"] .scene-tagline .line2 { color: #ffd66e; }
        .scene-panel[data-time="night"]  .scene-tagline .line1 { color: #fff; }
        .scene-panel[data-time="night"]  .scene-tagline .line2 { color: #a3e6ff; }

        .scene-tagline .line1 { animation: tagFadeUp 0.9s ease-out 0.4s forwards; }
        .scene-tagline .line2 { animation: tagFadeUp 0.9s ease-out 0.9s forwards; margin-top: 2px; }

        @keyframes tagFadeUp {
            0%   { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* ============ SVG ANIMATIONS ============ */

        /* Sun: dawn + noon + sunset */
        @keyframes sunHaloPulse { 0%,100% { transform: scale(1); opacity: 0.7; } 50% { transform: scale(1.08); opacity: 1; } }
        @keyframes sunCoreGlow  { 0%,100% { filter: drop-shadow(0 0 8px #ffd66e); } 50% { filter: drop-shadow(0 0 22px #fff8d6); } }
        .sun-halo { animation: sunHaloPulse 4s ease-in-out infinite; transform-origin: center; transform-box: fill-box; }
        .sun-core { animation: sunCoreGlow 3s ease-in-out infinite; }

        /* Moon glow (night) */
        @keyframes moonGlow { 0%,100% { filter: drop-shadow(0 0 6px rgba(255,248,214,0.5)); } 50% { filter: drop-shadow(0 0 18px rgba(255,248,214,0.9)); } }
        .moon-glow { animation: moonGlow 4s ease-in-out infinite; }

        /* Clouds drifting horizontally across the panel */
        @keyframes cloudDrift {
            0%   { transform: translateX(0); }
            100% { transform: translateX(500px); }
        }
        .cloud-a { animation: cloudDrift 55s linear infinite; }
        .cloud-b { animation: cloudDrift 70s linear infinite; animation-delay: -22s; }
        .cloud-c { animation: cloudDrift 60s linear infinite; animation-delay: -40s; }

        /* Stars twinkle */
        @keyframes starTwinkle { 0%,100% { opacity: 0.3; transform: scale(0.7); } 50% { opacity: 1; transform: scale(1.5); } }
        .star { animation: starTwinkle 2.5s ease-in-out infinite; transform-origin: center; transform-box: fill-box; }

        /* === LIGHTHOUSE BEAM — FIXED === */
        /* Wrap beam inside a <g> with transform="translate(80 296)" so origin becomes 0,0 of the inner group */
        @keyframes beamSweep {
            0%   { transform: rotate(-28deg); }
            50%  { transform: rotate(28deg);  }
            100% { transform: rotate(-28deg); }
        }
        .beam-inner { animation: beamSweep 7s ease-in-out infinite; transform-origin: 0 0; }
        @keyframes lampPulse { 0%,100% { opacity: 0.85; filter: drop-shadow(0 0 4px #ffd66e); } 50% { opacity: 1; filter: drop-shadow(0 0 14px #fff8d6); } }
        .lamp { animation: lampPulse 2s ease-in-out infinite; }

        /* Waves sway */
        @keyframes wave1 { 0%,100% { transform: translateX(0); } 50% { transform: translateX(-14px); } }
        @keyframes wave2 { 0%,100% { transform: translateX(0); } 50% { transform: translateX(18px); } }
        @keyframes wave3 { 0%,100% { transform: translateX(0); } 50% { transform: translateX(-10px); } }
        @keyframes wave4 { 0%,100% { transform: translateX(0); } 50% { transform: translateX(12px); } }
        .w1 { animation: wave1 5s ease-in-out infinite; }
        .w2 { animation: wave2 6s ease-in-out infinite; }
        .w3 { animation: wave3 4.5s ease-in-out infinite; }
        .w4 { animation: wave4 7s ease-in-out infinite; }

        @keyframes foamFade { 0%,100% { opacity: 0.8; } 50% { opacity: 0.3; } }
        .foam { animation: foamFade 3s ease-in-out infinite; }

        /* Sparkles */
        @keyframes sparkle { 0%,100% { opacity: 0; transform: scale(0.4); } 50% { opacity: 1; transform: scale(1.6); } }
        .sparkle-dot { animation: sparkle 2s ease-in-out infinite; transform-origin: center; transform-box: fill-box; }

        /* Birds flying across (V formation) — single group translates, individual birds flap */
        @keyframes birdFly {
            0%   { transform: translate(-60px, 0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translate(500px, -25px); opacity: 0; }
        }
        @keyframes wingFlap {
            0%,100% { transform: scaleY(1); }
            50%     { transform: scaleY(0.5); }
        }
        .birds-group { animation: birdFly 22s linear infinite; }
        .bird { animation: wingFlap 0.45s ease-in-out infinite; transform-origin: center; transform-box: fill-box; }

        /* Paper boats sailing */
        @keyframes sail1 {
            0%   { transform: translate(380px, 460px); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translate(140px, 452px); opacity: 0; }
        }
        @keyframes sail2 {
            0%   { transform: translate(390px, 495px); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translate(150px, 488px); opacity: 0; }
        }
        @keyframes sail3 {
            0%   { transform: translate(400px, 530px); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translate(160px, 522px); opacity: 0; }
        }
        .boat-1 { animation: sail1 14s linear infinite; }
        .boat-2 { animation: sail2 16s linear infinite; animation-delay: -5s; }
        .boat-3 { animation: sail3 13s linear infinite; animation-delay: -9s; }

        /* Hot air balloon (dawn) */
        @keyframes balloonDrift {
            0%   { transform: translate(60px, 250px); }
            50%  { transform: translate(320px, 180px); }
            100% { transform: translate(60px, 250px); }
        }
        .balloon { animation: balloonDrift 55s ease-in-out infinite; }

        /* Sky lanterns rising (sunset/night) */
        @keyframes lanternRise {
            0%   { transform: translateY(0); opacity: 0; }
            15%  { opacity: 1; }
            85%  { opacity: 1; }
            100% { transform: translateY(-280px); opacity: 0; }
        }
        .lantern { animation: lanternRise 11s ease-in-out infinite; }

        /* Shooting star */
        @keyframes shootingStar {
            0%   { transform: translate(0,0); opacity: 0; }
            10%  { opacity: 1; }
            70%  { opacity: 1; }
            100% { transform: translate(-180px,120px); opacity: 0; }
        }
        .shooting-star { animation: shootingStar 5s linear infinite; }

        /* Aurora ribbons (night) */
        @keyframes auroraDrift { 0%,100% { transform: translateX(0); opacity: 0.6; } 50% { transform: translateX(-25px); opacity: 1; } }
        .aurora-1 { animation: auroraDrift 10s ease-in-out infinite; }
        .aurora-2 { animation: auroraDrift 12s ease-in-out infinite reverse; }

        /* Fireflies (sunset) */
        @keyframes fireflyFloat {
            0%,100% { transform: translate(0,0); opacity: 0.3; }
            25%     { transform: translate(15px,-20px); opacity: 1; }
            50%     { transform: translate(-10px,-35px); opacity: 0.6; }
            75%     { transform: translate(18px,-15px); opacity: 1; }
        }
        .firefly { animation: fireflyFloat 6s ease-in-out infinite; }

        /* Floating dust */
        @keyframes dustFloat { 0%,100% { transform: translate(0,0); opacity: 0.3; } 50% { transform: translate(12px,-22px); opacity: 0.9; } }
        .dust { animation: dustFloat 5s ease-in-out infinite; }

        /* ============ RIGHT FORM PANEL ============ */
        .login-right {
            width: 430px;
            background: #ffffff;
            padding: 32px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            position: relative;
        }
        .login-right::-webkit-scrollbar { width: 0; display: none; }
        .login-right { scrollbar-width: none; }

        .decor-curve {
            position: absolute;
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
        }
        .decor-curve.top-right { top: 0; right: 0; width: 130px; height: 130px; }
        .decor-curve.bottom-left { bottom: 0; left: 0; width: 100px; height: 100px; }

        .login-right > * { position: relative; z-index: 2; }

        .secure-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            background: #e1f5ee;
            border-radius: 20px;
            font-size: 10px;
            color: #0f6e56;
            font-weight: 600;
            margin-bottom: 10px;
            align-self: flex-start;
        }
        .secure-pill .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #10b981;
            animation: pulseDot 1.5s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
            50%     { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
        }

        .login-right h2 {
            font-size: 23px;
            font-weight: 800;
            color: #0a1f3d;
            letter-spacing: -0.4px;
            margin: 0 0 3px;
        }
        .welcome-sub { font-size: 12.5px; color: #5a6b80; margin: 0; }

        .role-tabs {
            display: flex;
            gap: 4px;
            padding: 4px;
            background: #f0f4fa;
            border-radius: 11px;
            margin: 16px 0 14px;
            list-style: none;
            border: none !important;
        }
        .role-tabs .nav-item { flex: 1; display: block; }
        .role-tabs .nav-link {
            width: 100%;
            padding: 8px 6px !important;
            border-radius: 8px !important;
            border: none !important;
            background: transparent;
            color: #5a6b80;
            font-weight: 600;
            font-size: 11.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin: 0 !important;
            transition: all 0.2s;
        }
        .role-tabs .nav-link.active {
            background: #fff !important;
            color: #1e5fa8 !important;
            box-shadow: 0 1px 3px rgba(30,95,168,0.2);
        }
        .role-tabs .nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.6);
            color: #0a1f3d;
        }

        .login-right .form-label {
            font-size: 10px !important;
            font-weight: 700;
            color: #5a6b80;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 5px !important;
        }
        .login-right .mb-3 { margin-bottom: 10px !important; }

        .field-wrap { position: relative; }
        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            color: #94a3b8;
            transition: color 0.2s;
            z-index: 2;
            pointer-events: none;
        }

        .login-right .form-control-lg {
            background: #ffffff;
            border: 1.5px solid #dde4ee;
            border-radius: 10px;
            font-size: 13px;
            padding: 10px 12px 10px 38px;
            transition: all 0.2s;
        }
        .login-right .form-control-lg:focus {
            border-color: #1e5fa8;
            box-shadow: 0 0 0 3px rgba(30,95,168,0.12);
        }
        .field-wrap:focus-within .field-icon { color: #1e5fa8; }

        .input-group .form-control-lg { padding-right: 44px; }
        .pw-toggle-btn {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: #94a3b8;
            cursor: pointer;
            z-index: 3;
        }
        .pw-toggle-btn:hover { color: #1e5fa8; }

        .pw-meter {
            display: flex;
            gap: 3px;
            margin-top: 6px;
            height: 3px;
        }
        .pw-meter .bar {
            flex: 1;
            background: #e2e8f0;
            border-radius: 2px;
            transition: background 0.3s;
        }

        .login-right .form-check-label.small { font-size: 12px; color: #5a6b80; }

        .login-right .btn-primary {
            background: linear-gradient(135deg, #1e5fa8, #2d7dd2);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            padding: 11px;
            font-size: 13.5px;
            box-shadow: 0 4px 14px rgba(30,95,168,0.28);
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
            margin-top: 4px;
        }
        .login-right .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(30,95,168,0.4);
        }
        .login-right .btn-primary .btn-shine {
            position: absolute; top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s;
        }
        .login-right .btn-primary:hover .btn-shine { left: 150%; }

        .nok-radio {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 12.5px;
        }
        .nok-radio.active {
            border-color: #1e5fa8 !important;
            background: #e6f1fb !important;
        }

        .support-link {
            text-align: center;
            margin-top: 14px;
            font-size: 11.5px;
            color: #5a6b80;
        }
        .support-link a {
            color: #1e5fa8;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .scene-panel { display: none; }
            .login-right { width: 100%; padding: 24px 24px; }
            .login-shell { max-width: 460px; height: auto; max-height: calc(100vh - 28px); }
        }
        @media (max-height: 640px) {
            .login-right { padding: 16px 24px; }
            .scene-tagline .line1, .scene-tagline .line2 { font-size: 18px; }
        }
    </style>
</head>

<body>

    <div class="login-shell">

        {{-- ============ LEFT: ANIMATED SCENE ============ --}}
        <div class="scene-panel" data-time="dawn" id="scenePanel">

            <svg class="scene-svg" viewBox="0 60 400 540" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Lighthouse over the sea, changing with the time of day">

                <defs>
                    <radialGradient id="sunGradDawn" cx="0.5" cy="0.5" r="0.5">
                        <stop offset="0%" stop-color="#fff8d6"/>
                        <stop offset="55%" stop-color="#ffd66e"/>
                        <stop offset="100%" stop-color="rgba(255,180,90,0)"/>
                    </radialGradient>
                    <radialGradient id="sunGradNoon" cx="0.5" cy="0.5" r="0.5">
                        <stop offset="0%" stop-color="#ffffff"/>
                        <stop offset="50%" stop-color="#fff8d6"/>
                        <stop offset="100%" stop-color="rgba(255,255,200,0)"/>
                    </radialGradient>
                    <radialGradient id="sunGradSunset" cx="0.5" cy="0.5" r="0.5">
                        <stop offset="0%" stop-color="#fff8d6"/>
                        <stop offset="40%" stop-color="#ff9a5a"/>
                        <stop offset="100%" stop-color="rgba(255,90,90,0)"/>
                    </radialGradient>
                    <radialGradient id="sunReflectDawn" cx="0.5" cy="0" r="0.8">
                        <stop offset="0%" stop-color="rgba(255,236,180,0.6)"/>
                        <stop offset="100%" stop-color="rgba(255,236,180,0)"/>
                    </radialGradient>
                    <radialGradient id="sunReflectSunset" cx="0.5" cy="0" r="0.8">
                        <stop offset="0%" stop-color="rgba(255,154,90,0.65)"/>
                        <stop offset="100%" stop-color="rgba(255,154,90,0)"/>
                    </radialGradient>
                    <linearGradient id="beamGrad" x1="1" x2="0">
                        <stop offset="0%" stop-color="rgba(255,236,180,0)"/>
                        <stop offset="100%" stop-color="rgba(255,236,180,0.8)"/>
                    </linearGradient>
                    <linearGradient id="seaDawn1"   x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#7fc4e8"/><stop offset="100%" stop-color="#4a9bcc"/></linearGradient>
                    <linearGradient id="seaDawn2"   x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#5fb0d8"/><stop offset="100%" stop-color="#3a85b8"/></linearGradient>
                    <linearGradient id="seaNoon1"   x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#4fc3f7"/><stop offset="100%" stop-color="#0288d1"/></linearGradient>
                    <linearGradient id="seaNoon2"   x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#29b6f6"/><stop offset="100%" stop-color="#01579b"/></linearGradient>
                    <linearGradient id="seaSunset1" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#ff8a65"/><stop offset="100%" stop-color="#c2185b"/></linearGradient>
                    <linearGradient id="seaSunset2" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#e57373"/><stop offset="100%" stop-color="#7b1fa2"/></linearGradient>
                    <linearGradient id="seaNight1"  x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#3a6a9e"/><stop offset="100%" stop-color="#1a3b6e"/></linearGradient>
                    <linearGradient id="seaNight2"  x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#2d5a9e"/><stop offset="100%" stop-color="#0f2952"/></linearGradient>
                </defs>

                {{-- ======================================================
                     SKY LAYER (per time)
                ====================================================== --}}

                {{-- DAWN sky --}}
                <g class="only-dawn">
                    <circle class="sun-halo" cx="280" cy="190" r="90" fill="url(#sunGradDawn)" opacity="0.75"/>
                    <circle cx="280" cy="190" r="38" fill="#fff8d6"/>
                    <circle class="sun-core" cx="280" cy="190" r="30" fill="#ffd66e"/>
                    {{-- Clouds --}}
                    <g class="cloud-a" opacity="0.9">
                        <ellipse cx="60"  cy="100" rx="38" ry="11" fill="#fff"/>
                        <ellipse cx="85"  cy="95"  rx="28" ry="9"  fill="#fff"/>
                        <ellipse cx="40"  cy="105" rx="22" ry="8"  fill="#fff"/>
                    </g>
                    <g class="cloud-b" opacity="0.8">
                        <ellipse cx="-30" cy="140" rx="32" ry="9" fill="#fff"/>
                        <ellipse cx="-10" cy="135" rx="24" ry="7" fill="#fff"/>
                    </g>
                    {{-- Birds V formation --}}
                    <g class="birds-group">
                        <path class="bird" d="M0 150 Q3 146 6 150 Q9 146 12 150" stroke="#3a4a6e" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                        <path class="bird" d="M16 158 Q19 154 22 158 Q25 154 28 158" stroke="#3a4a6e" stroke-width="1.3" fill="none" stroke-linecap="round" style="animation-delay:0.1s;"/>
                        <path class="bird" d="M32 166 Q35 162 38 166 Q41 162 44 166" stroke="#3a4a6e" stroke-width="1.3" fill="none" stroke-linecap="round" style="animation-delay:0.2s;"/>
                    </g>
                    {{-- Mountains --}}
                    <path d="M0 320 L60 280 L120 305 L180 270 L240 295 L300 275 L360 290 L400 280 L400 360 L0 360 Z" fill="#7a9bc4" opacity="0.5"/>
                    <path d="M0 340 L80 310 L160 325 L240 300 L320 320 L400 305 L400 360 L0 360 Z" fill="#5a85b4" opacity="0.6"/>
                    {{-- Sun reflection --}}
                    <ellipse cx="280" cy="380" rx="100" ry="35" fill="url(#sunReflectDawn)"/>
                    {{-- Hot air balloon --}}
                    <g class="balloon">
                        <ellipse cx="0" cy="0" rx="14" ry="16" fill="#ff7e8a"/>
                        <path d="M-14 0 Q-14 8 -8 14 L8 14 Q14 8 14 0" fill="#ff5a6e" opacity="0.6"/>
                        <line x1="-10" y1="14" x2="-3" y2="18" stroke="#3a2a1a" stroke-width="0.5"/>
                        <line x1="10"  y1="14" x2="3"  y2="18" stroke="#3a2a1a" stroke-width="0.5"/>
                        <rect x="-4" y="18" width="8" height="5" rx="1" fill="#8a5a3a"/>
                    </g>
                </g>

                {{-- NOON sky --}}
                <g class="only-noon">
                    <circle class="sun-halo" cx="200" cy="120" r="90" fill="url(#sunGradNoon)" opacity="0.9"/>
                    <circle cx="200" cy="120" r="34" fill="#ffffff"/>
                    <circle class="sun-core" cx="200" cy="120" r="26" fill="#fff8d6"/>
                    {{-- Multiple fluffy clouds drifting --}}
                    <g class="cloud-a" opacity="0.95">
                        <ellipse cx="-40" cy="80" rx="34" ry="11" fill="#fff"/>
                        <ellipse cx="-15" cy="75" rx="26" ry="10" fill="#fff"/>
                        <ellipse cx="-58" cy="85" rx="20" ry="8" fill="#fff"/>
                    </g>
                    <g class="cloud-b" opacity="0.9">
                        <ellipse cx="-80" cy="160" rx="40" ry="12" fill="#fff"/>
                        <ellipse cx="-55" cy="154" rx="28" ry="10" fill="#fff"/>
                    </g>
                    <g class="cloud-c" opacity="0.85">
                        <ellipse cx="-20" cy="220" rx="32" ry="9" fill="#fff"/>
                        <ellipse cx="0"   cy="216" rx="22" ry="7" fill="#fff"/>
                    </g>
                    {{-- Seagulls --}}
                    <g class="birds-group" style="animation-duration:18s;">
                        <path class="bird" d="M0 240 Q4 235 8 240 Q12 235 16 240" stroke="#2d4a6e" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                        <path class="bird" d="M22 250 Q26 245 30 250 Q34 245 38 250" stroke="#2d4a6e" stroke-width="1.5" fill="none" stroke-linecap="round" style="animation-delay:0.15s;"/>
                    </g>
                    {{-- Mountains brighter --}}
                    <path d="M0 320 L70 275 L140 305 L210 265 L280 295 L350 275 L400 285 L400 360 L0 360 Z" fill="#7fb2e2" opacity="0.55"/>
                    <path d="M0 340 L90 312 L180 325 L270 300 L360 320 L400 308 L400 360 L0 360 Z" fill="#5a9bd4" opacity="0.65"/>
                </g>

                {{-- SUNSET sky --}}
                <g class="only-sunset">
                    <circle class="sun-halo" cx="220" cy="280" r="110" fill="url(#sunGradSunset)" opacity="0.8"/>
                    <circle cx="220" cy="280" r="44" fill="#ff9a5a"/>
                    <circle class="sun-core" cx="220" cy="280" r="34" fill="#ff7e5f"/>
                    {{-- Wispy clouds --}}
                    <g class="cloud-a" opacity="0.75">
                        <ellipse cx="-30" cy="120" rx="42" ry="9" fill="#c44569"/>
                        <ellipse cx="0"   cy="115" rx="30" ry="7" fill="#d35480"/>
                    </g>
                    <g class="cloud-b" opacity="0.7">
                        <ellipse cx="-20" cy="170" rx="46" ry="10" fill="#6b3fa0"/>
                        <ellipse cx="10"  cy="165" rx="28" ry="8" fill="#8e5fc4"/>
                    </g>
                    <g class="cloud-c" opacity="0.65">
                        <ellipse cx="-40" cy="210" rx="36" ry="8" fill="#c44569"/>
                    </g>
                    {{-- Silhouette mountains --}}
                    <path d="M0 320 L60 270 L130 295 L200 255 L270 285 L340 265 L400 275 L400 360 L0 360 Z" fill="#3a2654" opacity="0.7"/>
                    <path d="M0 340 L90 305 L180 320 L270 295 L360 315 L400 300 L400 360 L0 360 Z" fill="#2a1845" opacity="0.85"/>
                    {{-- Sun reflection --}}
                    <ellipse cx="220" cy="420" rx="120" ry="45" fill="url(#sunReflectSunset)"/>
                    {{-- Fireflies --}}
                    <circle class="firefly" cx="100" cy="280" r="1.8" fill="#ffd66e"/>
                    <circle class="firefly" cx="160" cy="320" r="1.5" fill="#ffd66e" style="animation-delay:1s;"/>
                    <circle class="firefly" cx="350" cy="270" r="1.8" fill="#ffd66e" style="animation-delay:2s;"/>
                    <circle class="firefly" cx="60"  cy="350" r="1.5" fill="#ffd66e" style="animation-delay:0.5s;"/>
                    {{-- Sky lanterns --}}
                    <g class="lantern" style="animation-delay:0s;">
                        <rect x="120" y="350" width="6" height="9" rx="1" fill="#ff9a76"/>
                        <circle cx="123" cy="354.5" r="3.5" fill="#ffd66e" opacity="0.9"/>
                    </g>
                    <g class="lantern" style="animation-delay:-4s;">
                        <rect x="290" y="360" width="5" height="8" rx="1" fill="#ff7e8a"/>
                        <circle cx="292.5" cy="364" r="3" fill="#ffd66e" opacity="0.9"/>
                    </g>
                </g>

                {{-- NIGHT sky --}}
                <g class="only-night">
                    {{-- Crescent moon --}}
                    <g class="moon-glow">
                        <circle cx="320" cy="100" r="30" fill="#f5e6c8" opacity="0.95"/>
                        <circle cx="332" cy="92" r="26" fill="#0f2952"/>
                    </g>
                    {{-- Stars scattered --}}
                    <circle class="star" cx="50"  cy="60"  r="1.4" fill="#fff"/>
                    <circle class="star" cx="90"  cy="40"  r="2"   fill="#fff" style="animation-delay:0.3s;"/>
                    <circle class="star" cx="140" cy="70"  r="1.2" fill="#fff" style="animation-delay:0.6s;"/>
                    <circle class="star" cx="180" cy="35"  r="1.6" fill="#fff" style="animation-delay:0.9s;"/>
                    <circle class="star" cx="240" cy="55"  r="1.4" fill="#fff" style="animation-delay:1.2s;"/>
                    <circle class="star" cx="280" cy="30"  r="2"   fill="#fff" style="animation-delay:1.5s;"/>
                    <circle class="star" cx="370" cy="65"  r="1.5" fill="#fff" style="animation-delay:1.8s;"/>
                    <circle class="star" cx="40"  cy="130" r="1.2" fill="#fff" style="animation-delay:0.4s;"/>
                    <circle class="star" cx="200" cy="140" r="1.5" fill="#fff" style="animation-delay:1.0s;"/>
                    <circle class="star" cx="370" cy="130" r="1.6" fill="#fff" style="animation-delay:1.6s;"/>
                    <circle class="star" cx="120" cy="180" r="1.3" fill="#fff" style="animation-delay:0.8s;"/>
                    <circle class="star" cx="260" cy="200" r="1.2" fill="#fff" style="animation-delay:1.4s;"/>
                    {{-- Aurora ribbons --}}
                    <path class="aurora-1" d="M-30 80 Q100 50 200 90 T420 70 L420 130 Q300 100 200 140 T-30 120 Z" fill="rgba(126,231,208,0.15)"/>
                    <path class="aurora-2" d="M-30 110 Q120 80 220 120 T420 100 L420 160 Q300 130 220 170 T-30 150 Z" fill="rgba(163,230,255,0.12)"/>
                    {{-- Constellation heart --}}
                    <polyline points="200,200 180,180 165,185 155,200 200,240 245,200 235,185 220,180 200,200"
                              fill="none" stroke="rgba(255,214,110,0.5)" stroke-width="0.8" stroke-linecap="round"/>
                    <circle class="star" cx="200" cy="200" r="2" fill="#ffd66e"/>
                    <circle class="star" cx="180" cy="180" r="2" fill="#ffd66e" style="animation-delay:0.2s;"/>
                    <circle class="star" cx="165" cy="185" r="2" fill="#ffd66e" style="animation-delay:0.4s;"/>
                    <circle class="star" cx="155" cy="200" r="2" fill="#ffd66e" style="animation-delay:0.6s;"/>
                    <circle class="star" cx="200" cy="240" r="2" fill="#ffd66e" style="animation-delay:0.8s;"/>
                    <circle class="star" cx="245" cy="200" r="2" fill="#ffd66e" style="animation-delay:1s;"/>
                    <circle class="star" cx="235" cy="185" r="2" fill="#ffd66e" style="animation-delay:1.2s;"/>
                    <circle class="star" cx="220" cy="180" r="2" fill="#ffd66e" style="animation-delay:1.4s;"/>
                    {{-- Shooting stars --}}
                    <g class="shooting-star" transform="translate(380, 60)">
                        <line x1="0" y1="0" x2="30" y2="-20" stroke="#fff" stroke-width="1.2" opacity="0.8"/>
                        <circle cx="0" cy="0" r="1.5" fill="#fff"/>
                    </g>
                    <g class="shooting-star" style="animation-delay:7s;" transform="translate(300, 100)">
                        <line x1="0" y1="0" x2="25" y2="-15" stroke="#fff" stroke-width="1" opacity="0.7"/>
                        <circle cx="0" cy="0" r="1.2" fill="#fff"/>
                    </g>
                    {{-- Mountains silhouette --}}
                    <path d="M0 320 L60 280 L120 305 L180 270 L240 295 L300 275 L360 290 L400 280 L400 360 L0 360 Z" fill="#1a2d4a" opacity="0.85"/>
                    {{-- Sky lanterns --}}
                    <g class="lantern" style="animation-delay:0s;">
                        <rect x="100" y="330" width="6" height="9" rx="1" fill="#ff9a76"/>
                        <circle cx="103" cy="334.5" r="3.5" fill="#ffd66e" opacity="0.9"/>
                    </g>
                    <g class="lantern" style="animation-delay:-3s;">
                        <rect x="160" y="360" width="5" height="8" rx="1" fill="#ff7e8a"/>
                        <circle cx="162.5" cy="364" r="3" fill="#ffd66e" opacity="0.9"/>
                    </g>
                    <g class="lantern" style="animation-delay:-6s;">
                        <rect x="240" y="340" width="6" height="9" rx="1" fill="#ffd66e"/>
                        <circle cx="243" cy="344.5" r="3.5" fill="#fff5c4" opacity="0.9"/>
                    </g>
                    <g class="lantern" style="animation-delay:-9s;">
                        <rect x="320" y="370" width="5" height="8" rx="1" fill="#a3e6ff"/>
                        <circle cx="322.5" cy="374" r="3" fill="#fff5c4" opacity="0.9"/>
                    </g>
                </g>

                {{-- ======================================================
                     LIGHTHOUSE BEAM (only when DARK enough — sunset/night)
                ====================================================== --}}
                <g class="only-sunset">
                    <g transform="translate(80 296)">
                        <g class="beam-inner">
                            <path d="M0 0 L-230 -130 L-230 130 Z" fill="url(#beamGrad)" opacity="0.5"/>
                            <path d="M0 0 L-200 -80 L-200 80 Z"   fill="rgba(255,236,180,0.35)"/>
                        </g>
                    </g>
                </g>
                <g class="only-night">
                    <g transform="translate(80 296)">
                        <g class="beam-inner">
                            <path d="M0 0 L-230 -130 L-230 130 Z" fill="url(#beamGrad)" opacity="0.55"/>
                            <path d="M0 0 L-200 -80 L-200 80 Z"   fill="rgba(255,236,180,0.4)"/>
                        </g>
                    </g>
                </g>

                {{-- ======================================================
                     CLIFF & LIGHTHOUSE (always visible)
                ====================================================== --}}
                <path d="M0 400 Q40 380 80 385 Q120 390 145 405 L145 620 L0 620 Z" fill="#3a6a9e" opacity="0.85"/>
                <path d="M0 415 Q40 398 80 402 Q120 408 145 420 L145 620 L0 620 Z" fill="#4a7fb4" opacity="0.9"/>
                <path d="M20 398 Q30 392 40 398 M60 388 Q70 383 80 388 M100 392 Q110 387 120 392" stroke="#7ec98a" stroke-width="2" fill="none" stroke-linecap="round"/>

                <g>
                    <rect x="62" y="330" width="36" height="50" fill="#fafafa"/>
                    <rect x="62" y="330" width="36" height="8"  fill="#e24b4a"/>
                    <rect x="62" y="352" width="36" height="8"  fill="#e24b4a"/>
                    <rect x="62" y="374" width="36" height="6"  fill="#e24b4a"/>
                    <polygon points="66,330 94,330 90,305 70,305" fill="#fafafa"/>
                    <rect x="68" y="285" width="24" height="22" fill="#2d4a6e"/>
                    <rect x="68" y="285" width="24" height="4"  fill="#fafafa"/>
                    <rect x="68" y="303" width="24" height="4"  fill="#fafafa"/>
                    <circle class="lamp" cx="80" cy="296" r="6" fill="#fff8d6"/>
                    <polygon points="64,285 96,285 80,263" fill="#e24b4a"/>
                    <circle cx="80" cy="260" r="2.5" fill="#ffd66e"/>
                    <rect x="74" y="340" width="4" height="6"  fill="#ffd66e" opacity="0.9"/>
                    <rect x="82" y="340" width="4" height="6"  fill="#ffd66e" opacity="0.9"/>
                    <rect x="78" y="362" width="4" height="6"  fill="#ffd66e" opacity="0.9"/>
                    <rect x="76" y="368" width="8" height="12" rx="4" fill="#5a3a1f"/>
                </g>

                {{-- ======================================================
                     WAVES (color via time)
                ====================================================== --}}
                <path id="wave1" class="w1" d="M-40 410 Q50 395 140 410 T320 410 T500 410 L500 620 L-40 620 Z"/>
                <path id="wave2" class="w2" d="M-40 445 Q60 425 160 445 T340 445 T520 445 L520 620 L-40 620 Z"/>
                <path id="wave3" class="w3" d="M-40 485 Q70 467 170 485 T350 485 T530 485 L530 620 L-40 620 Z"/>
                <path id="wave4" class="w4" d="M-40 525 Q80 510 180 525 T360 525 T540 525 L540 620 L-40 620 Z"/>

                <path class="foam" d="M40 408 Q70 400 100 408 M180 407 Q210 400 240 407 M280 410 Q310 402 340 410" stroke="#fff" stroke-width="1.5" fill="none" opacity="0.7" stroke-linecap="round"/>
                <path class="foam" d="M60 443 Q90 437 120 443 M200 442 Q230 437 260 442 M310 444 Q340 438 370 444" stroke="#fff" stroke-width="1.3" fill="none" opacity="0.6" stroke-linecap="round" style="animation-delay:1s;"/>

                <circle class="sparkle-dot" cx="220" cy="395" r="1.5" fill="#fff"/>
                <circle class="sparkle-dot" cx="280" cy="405" r="2"   fill="#fff" style="animation-delay:0.3s;"/>
                <circle class="sparkle-dot" cx="320" cy="390" r="1.5" fill="#fff" style="animation-delay:0.7s;"/>
                <circle class="sparkle-dot" cx="250" cy="430" r="1.5" fill="#fff" style="animation-delay:1.1s;"/>
                <circle class="sparkle-dot" cx="340" cy="435" r="1.8" fill="#fff" style="animation-delay:1.5s;"/>

                {{-- Paper boats sailing toward lighthouse --}}
                <g class="boat-1">
                    <polygon points="0,0 22,0 11,8" fill="#fff"/>
                    <polygon points="6,-8 11,8 11,-8" fill="#ffd66e"/>
                    <polygon points="11,-8 16,-8 11,8" fill="#ff9a76"/>
                </g>
                <g class="boat-2">
                    <polygon points="0,0 18,0 9,6" fill="#fff"/>
                    <polygon points="5,-7 9,6 9,-7" fill="#7ee7d0"/>
                    <polygon points="9,-7 13,-7 9,6" fill="#a3e6ff"/>
                </g>
                <g class="boat-3">
                    <polygon points="0,0 16,0 8,5" fill="#fff"/>
                    <polygon points="4,-6 8,5 8,-6" fill="#ff9a76"/>
                    <polygon points="8,-6 12,-6 8,5" fill="#ffd66e"/>
                </g>

                {{-- Floating dust --}}
                <circle class="dust" cx="150" cy="220" r="1.2" fill="#fff5c4"/>
                <circle class="dust" cx="320" cy="160" r="1"   fill="#fff" style="animation-delay:1s;"/>
                <circle class="dust" cx="100" cy="200" r="1.5" fill="#fff5c4" style="animation-delay:2s;"/>
                <circle class="dust" cx="370" cy="240" r="1.2" fill="#fff" style="animation-delay:0.5s;"/>

            </svg>

            {{-- Logo + time badge --}}
            <div class="scene-overlay-top">
                <div class="brand-block">
                    <div class="brand-logo">
                        <i class="bi bi-life-preserver"></i>
                    </div>
                    <div>
                        <div class="brand-title">e-Tawassul</div>
                        <div class="brand-sub">IIUM Crisis &amp; Legacy</div>
                    </div>
                </div>
                <button type="button" class="time-badge" id="timeBadge" title="Cycle scene">
                    <i class="bi bi-sun" id="timeIcon"></i>
                    <span id="timeLabel">Dawn</span>
                </button>
            </div>

            <div class="scene-tagline">
                <div class="line1">When crisis strikes,</div>
                <div class="line2">support is already here.</div>
            </div>
        </div>

        {{-- ============ RIGHT: FORM ============ --}}
        <div class="login-right">
            <svg class="decor-curve top-right" viewBox="0 0 130 130">
                <path d="M130 0 Q130 65 65 65 Q65 130 0 130" stroke="#fde68a" stroke-width="1.2" fill="none"/>
                <path d="M130 18 Q130 75 75 75 Q75 130 18 130" stroke="#fbb985" stroke-width="1" fill="none"/>
                <path d="M130 36 Q130 85 85 85 Q85 130 36 130" stroke="#f4a3b8" stroke-width="0.8" fill="none"/>
            </svg>
            <svg class="decor-curve bottom-left" viewBox="0 0 100 100">
                <path d="M0 100 Q0 50 50 50 Q50 0 100 0" stroke="#a3d8ff" stroke-width="1" fill="none"/>
                <path d="M0 78 Q0 38 40 38 Q40 0 80 0" stroke="#7fb2e2" stroke-width="0.8" fill="none"/>
            </svg>

            <div class="secure-pill">
                <span class="dot"></span>
                Secure connection active
            </div>
            <h2>Welcome back</h2>
            <p class="welcome-sub">Sign in to e-Tawassul to continue</p>

            <div x-data="{
                    tab: '{{ old('role', 'student') }}',
                    delivery: '{{ old('delivery', 'email') }}',
                    setTab(t) { this.tab = t; document.getElementById('role').value = t; }
                 }">
                <ul class="nav role-tabs" role="tablist">
                    @foreach (['student' => ['label' => 'Student', 'icon' => 'bi-mortarboard'], 'admin' => ['label' => 'Admin', 'icon' => 'bi-shield-check'], 'nok' => ['label' => 'Next of Kin', 'icon' => 'bi-people']] as $val => $meta)
                        <li class="nav-item">
                            <button type="button" class="nav-link" :class="tab === '{{ $val }}' ? 'active' : ''"
                                @click="setTab('{{ $val }}')">
                                <i class="bi {{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('login.post') }}" novalidate>
                    @csrf
                    <input type="hidden" name="role" id="role" value="{{ old('role', 'student') }}">

                    <div class="mb-3">
                        <label class="form-label">
                            <span x-show="tab === 'student'">Matric ID or Email</span>
                            <span x-show="tab === 'admin'" x-cloak>Admin Email</span>
                            <span x-show="tab === 'nok'"   x-cloak>Registered Email</span>
                        </label>
                        <div class="field-wrap">
                            <i class="bi field-icon"
                               :class="tab === 'student' ? 'bi-person' : (tab === 'admin' ? 'bi-shield-check' : 'bi-people')"></i>
                            <input type="text" name="identifier" value="{{ old('identifier') }}"
                                class="form-control form-control-lg @error('identifier') is-invalid @enderror"
                                :placeholder="tab === 'student'
                                    ? '2225498 or matric/email'
                                    : (tab === 'nok' ? 'kin@example.com' : 'admin@iium.edu.my')"
                                required autofocus>
                            @error('identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3" x-data="{ showPassword: false }" x-show="tab !== 'nok'" x-cloak>
                        <label class="form-label">Password</label>
                        <div class="field-wrap input-group">
                            <i class="bi bi-lock field-icon"></i>
                            <input :type="showPassword ? 'text' : 'password'" name="password"
                                id="passwordInput"
                                class="form-control form-control-lg @error('password') is-invalid @enderror"
                                placeholder="••••••••"
                                :required="tab !== 'nok'">
                            <button class="pw-toggle-btn" type="button" @click="showPassword = !showPassword" aria-label="Show password">
                                <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="pw-meter" id="pwMeter">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                    </div>

                    <div class="mb-3" x-show="tab === 'nok'" x-cloak>
                        <label class="form-label mb-2">Send verification code by</label>
                        <div class="d-flex gap-2">
                            <label class="nok-radio flex-fill p-3 border rounded"
                                   :class="delivery === 'email' ? 'active' : ''">
                                <input type="radio" name="delivery" value="email"
                                       class="form-check-input me-2" x-model="delivery">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <label class="nok-radio flex-fill p-3 border rounded"
                                   :class="delivery === 'sms' ? 'active' : ''">
                                <input type="radio" name="delivery" value="sms"
                                       class="form-check-input me-2" x-model="delivery">
                                <i class="bi bi-phone"></i> SMS
                            </label>
                        </div>
                        <p class="form-text small mt-2">
                            <i class="bi bi-info-circle"></i>
                            A 4-digit code will be sent to your registered <span x-text="delivery === 'sms' ? 'phone' : 'email'"></span>.
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3" x-show="tab !== 'nok'" x-cloak>
                        <div class="form-check">
                            <input type="checkbox" name="remember" value="1" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label small">Keep me signed in</label>
                        </div>
                        <a href="#" class="small" style="color:#1e5fa8; text-decoration:none; font-weight:600;">Forgot password?</a>
                    </div>

                    @if ($errors->has('auth'))
                        <div class="alert alert-danger small">{{ $errors->first('auth') }}</div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <span class="btn-shine"></span>
                        <i class="bi bi-shield-lock"></i>
                        <span x-show="tab !== 'nok'">Sign In</span>
                        <span x-show="tab === 'nok'" x-cloak>Send Code</span>
                    </button>
                </form>
            </div>

            <div class="support-link">
                Need help? <a href="#">Reach out anytime</a>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const TIMES = ['dawn', 'noon', 'sunset', 'night'];
        const META = {
            dawn:   { label: 'Dawn',   icon: 'bi-sunrise' },
            noon:   { label: 'Noon',   icon: 'bi-sun' },
            sunset: { label: 'Sunset', icon: 'bi-sunset' },
            night:  { label: 'Night',  icon: 'bi-moon-stars' }
        };

        function getTimeOfDay() {
            const h = new Date().getHours();
            if (h >= 5  && h < 11) return 'dawn';
            if (h >= 11 && h < 17) return 'noon';
            if (h >= 17 && h < 20) return 'sunset';
            return 'night';
        }

        function applyTime(t) {
            const panel = document.getElementById('scenePanel');
            panel.setAttribute('data-time', t);
            document.getElementById('timeLabel').textContent = META[t].label;
            document.getElementById('timeIcon').className = 'bi ' + META[t].icon;

            // Update wave fills per time
            const waveFills = {
                dawn:   ['url(#seaDawn1)',   'url(#seaDawn2)',   '#5e9bd6', '#7fb2e2'],
                noon:   ['url(#seaNoon1)',   'url(#seaNoon2)',   '#4fc3f7', '#81d4fa'],
                sunset: ['url(#seaSunset1)', 'url(#seaSunset2)', '#ad5389', '#c44569'],
                night:  ['url(#seaNight1)',  'url(#seaNight2)',  '#2d5a9e', '#3a6a9e']
            };
            const opacities = [0.95, 0.9, 0.85, 0.85];
            for (let i = 1; i <= 4; i++) {
                const w = document.getElementById('wave' + i);
                if (w) {
                    w.setAttribute('fill', waveFills[t][i - 1]);
                    w.setAttribute('opacity', opacities[i - 1]);
                }
            }
        }

        const badge = document.getElementById('timeBadge');
        let manualOverride = false;

        applyTime(getTimeOfDay());

        badge.addEventListener('click', () => {
            const cur = document.getElementById('scenePanel').getAttribute('data-time');
            const next = TIMES[(TIMES.indexOf(cur) + 1) % TIMES.length];
            manualOverride = true;
            applyTime(next);
        });

        setInterval(() => {
            if (!manualOverride) {
                const auto = getTimeOfDay();
                if (auto !== document.getElementById('scenePanel').getAttribute('data-time')) {
                    applyTime(auto);
                }
            }
        }, 600000);

        // Password strength
        const pw = document.getElementById('passwordInput');
        if (pw) {
            const bars = document.querySelectorAll('#pwMeter .bar');
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#10b981'];
            pw.addEventListener('input', () => {
                const v = pw.value;
                let score = 0;
                if (v.length >= 6) score++;
                if (v.length >= 10) score++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
                bars.forEach((b, i) => {
                    b.style.background = i < score ? colors[score - 1] : '#e2e8f0';
                });
            });
        }
    })();
    </script>

</body>

</html>
