<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DairyFlow — Farm to Future</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --black:      #080A0C;
            --dark:       #0E1215;
            --dark-2:     #141920;
            --dark-3:     #1C2430;
            --gold:       #C9932A;
            --gold-lt:    #E8B84B;
            --gold-dim:   #7A5515;
            --cream:      #F0E6D0;
            --cream-dim:  #A89070;
            --green:      #2A6B3C;
            --green-lt:   #3D9B57;
            --white:      #FFFFFF;
            --border:     rgba(201,147,42,0.18);
            --border-dim: rgba(255,255,255,0.06);
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body:    'Outfit', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-body);
            background: var(--black);
            color: var(--cream);
            overflow-x: hidden;
            cursor: none;
        }

        /* ── CUSTOM CURSOR ── */
        .cursor {
            width: 12px; height: 12px;
            background: var(--gold);
            border-radius: 50%;
            position: fixed; top: 0; left: 0;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.15s ease, opacity 0.2s;
            transform: translate(-50%, -50%);
        }
        .cursor-ring {
            width: 36px; height: 36px;
            border: 1.5px solid rgba(201,147,42,0.5);
            border-radius: 50%;
            position: fixed; top: 0; left: 0;
            pointer-events: none;
            z-index: 9998;
            transition: transform 0.4s cubic-bezier(0.25,0.46,0.45,0.94);
            transform: translate(-50%, -50%);
        }
        a:hover ~ .cursor, button:hover ~ .cursor { transform: translate(-50%,-50%) scale(2.5); }

        /* ── NOISE OVERLAY ── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 1;
            opacity: 0.4;
        }

        /* ── NAVBAR ── */
        .nav {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.4rem 3rem;
            border-bottom: 1px solid var(--border-dim);
            backdrop-filter: blur(20px);
            background: rgba(8,10,12,0.7);
            animation: fadeDown 0.8s ease both;
        }
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .nav-logo {
            display: flex; align-items: center; gap: 10px;
            font-family: var(--font-display);
            font-size: 1.5rem;
            color: var(--gold-lt);
            letter-spacing: 0.5px;
            text-decoration: none;
        }
        .nav-logo .icon { font-size: 1.6rem; }
        .nav-links-top {
            display: flex; gap: 2rem; list-style: none;
        }
        .nav-links-top a {
            color: var(--cream-dim);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .nav-links-top a:hover { color: var(--gold-lt); }
        .nav-cta {
            background: var(--gold);
            color: var(--black);
            padding: 9px 22px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            letter-spacing: 0.3px;
            transition: all 0.2s;
            border: none; cursor: pointer;
        }
        .nav-cta:hover { background: var(--gold-lt); transform: translateY(-1px); }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            display: flex; align-items: center;
            position: relative;
            overflow: hidden;
            padding: 120px 3rem 80px;
        }

        /* Animated background grid */
        .hero-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(201,147,42,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,147,42,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridPan 20s linear infinite;
        }
        @keyframes gridPan {
            from { transform: translateY(0); }
            to   { transform: translateY(60px); }
        }

        /* Radial glow */
        .hero-glow {
            position: absolute;
            width: 800px; height: 800px;
            background: radial-gradient(circle, rgba(201,147,42,0.08) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .hero-glow-2 {
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(42,107,60,0.12) 0%, transparent 70%);
            bottom: 10%; right: 10%;
            pointer-events: none;
        }

        .hero-content {
            position: relative; z-index: 2;
            max-width: 760px;
        }

        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(201,147,42,0.1);
            border: 1px solid var(--border);
            color: var(--gold-lt);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2rem;
            animation: fadeUp 0.9s 0.2s ease both;
        }
        .hero-eyebrow .dot {
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        .hero h1 {
            font-family: var(--font-display);
            font-size: clamp(3rem, 6vw, 5.5rem);
            line-height: 1.08;
            font-weight: 900;
            color: var(--white);
            margin-bottom: 1.5rem;
            animation: fadeUp 0.9s 0.35s ease both;
        }
        .hero h1 em {
            font-style: italic;
            color: var(--gold-lt);
            position: relative;
        }
        .hero h1 em::after {
            content: '';
            position: absolute;
            bottom: 2px; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(to right, var(--gold), transparent);
            border-radius: 2px;
        }

        .hero-desc {
            font-size: 1.1rem;
            color: var(--cream-dim);
            line-height: 1.8;
            max-width: 540px;
            margin-bottom: 3rem;
            font-weight: 300;
            animation: fadeUp 0.9s 0.5s ease both;
        }

        .hero-actions {
            display: flex; gap: 1rem; flex-wrap: wrap;
            animation: fadeUp 0.9s 0.65s ease both;
        }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 10px;
            background: var(--gold);
            color: var(--black);
            padding: 15px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.25s;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }
        .btn-hero-primary::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 100%);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .btn-hero-primary:hover { background: var(--gold-lt); transform: translateY(-3px); box-shadow: 0 12px 40px rgba(201,147,42,0.35); }
        .btn-hero-primary:hover::before { opacity: 1; }
        .btn-hero-primary .arrow { transition: transform 0.2s; }
        .btn-hero-primary:hover .arrow { transform: translateX(4px); }

        .btn-hero-ghost {
            display: inline-flex; align-items: center; gap: 10px;
            background: transparent;
            color: var(--cream);
            padding: 15px 32px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.15);
            transition: all 0.25s;
        }
        .btn-hero-ghost:hover { border-color: var(--gold); color: var(--gold-lt); background: rgba(201,147,42,0.05); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Floating cow illustration */
        .hero-visual {
            position: absolute;
            right: 4%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            animation: fadeUp 1s 0.4s ease both;
        }
        .cow-circle {
            width: 380px; height: 380px;
            border-radius: 50%;
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 9rem;
            position: relative;
            background: radial-gradient(circle at 40% 40%, rgba(201,147,42,0.08), transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-18px); }
        }
        .cow-orbit {
            position: absolute; inset: -30px;
            border: 1px dashed rgba(201,147,42,0.2);
            border-radius: 50%;
            animation: spin 20s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .cow-orbit-dot {
            position: absolute; top: 10px; left: 50%;
            width: 8px; height: 8px;
            background: var(--gold);
            border-radius: 50%;
            transform: translateX(-50%);
            box-shadow: 0 0 12px var(--gold);
        }

        /* Scroll indicator */
        .scroll-hint {
            position: absolute; bottom: 2.5rem; left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            color: var(--cream-dim);
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            animation: fadeUp 1s 1s ease both;
        }
        .scroll-line {
            width: 1px; height: 50px;
            background: linear-gradient(to bottom, var(--gold), transparent);
            animation: scrollLine 1.8s ease-in-out infinite;
        }
        @keyframes scrollLine {
            0%   { transform: scaleY(0); transform-origin: top; }
            50%  { transform: scaleY(1); transform-origin: top; }
            51%  { transform: scaleY(1); transform-origin: bottom; }
            100% { transform: scaleY(0); transform-origin: bottom; }
        }

        /* ── STATS STRIP ── */
        .stats-strip {
            background: var(--dark-2);
            border-top: 1px solid var(--border-dim);
            border-bottom: 1px solid var(--border-dim);
            display: flex; flex-wrap: wrap;
        }
        .stat-box {
            flex: 1; min-width: 160px;
            padding: 2rem 2.5rem;
            border-right: 1px solid var(--border-dim);
            text-align: center;
            transition: background 0.2s;
        }
        .stat-box:last-child { border-right: none; }
        .stat-box:hover { background: rgba(201,147,42,0.05); }
        .stat-num {
            font-family: var(--font-display);
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--gold-lt);
            display: block;
            line-height: 1;
            margin-bottom: 0.4rem;
        }
        .stat-lbl { font-size: 0.8rem; color: var(--cream-dim); font-weight: 500; letter-spacing: 0.5px; }

        /* ── FEATURES ── */
        .section {
            padding: 100px 3rem;
            position: relative;
        }
        .section-label {
            display: inline-flex; align-items: center; gap: 8px;
            color: var(--gold);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .section-label::before {
            content: '';
            width: 24px; height: 1px;
            background: var(--gold);
        }
        .section-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 1.2rem;
        }
        .section-title em { font-style: italic; color: var(--gold-lt); }
        .section-sub { color: var(--cream-dim); font-size: 1rem; line-height: 1.7; max-width: 500px; font-weight: 300; }

        .features-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: var(--border-dim);
            border: 1px solid var(--border-dim);
            border-radius: 16px;
            overflow: hidden;
        }
        .feature-cell {
            background: var(--dark-2);
            padding: 2rem;
            transition: background 0.25s;
            position: relative;
            overflow: hidden;
        }
        .feature-cell::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(201,147,42,0.06), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .feature-cell:hover { background: var(--dark-3); }
        .feature-cell:hover::after { opacity: 1; }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }
        .feature-cell h3 {
            font-family: var(--font-display);
            font-size: 1.05rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        .feature-cell p {
            font-size: 0.85rem;
            color: var(--cream-dim);
            line-height: 1.6;
            font-weight: 300;
        }

        /* ── HOW IT WORKS ── */
        .steps-section {
            background: var(--dark-2);
            border-top: 1px solid var(--border-dim);
            border-bottom: 1px solid var(--border-dim);
        }
        .steps-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            margin-top: 4rem;
        }
        .step {
            padding: 2.5rem 2rem;
            border-right: 1px solid var(--border-dim);
            position: relative;
        }
        .step:last-child { border-right: none; }
        .step-number {
            font-family: var(--font-display);
            font-size: 3.5rem;
            font-weight: 900;
            color: rgba(201,147,42,0.12);
            line-height: 1;
            margin-bottom: 1.2rem;
            display: block;
        }
        .step-icon {
            font-size: 1.8rem;
            display: block;
            margin-bottom: 1rem;
        }
        .step h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
        }
        .step p {
            font-size: 0.82rem;
            color: var(--cream-dim);
            line-height: 1.65;
            font-weight: 300;
        }
        /* Connector arrow */
        .step:not(:last-child)::after {
            content: '→';
            position: absolute;
            top: 2.5rem;
            right: -0.7rem;
            color: var(--gold-dim);
            font-size: 1.1rem;
            z-index: 1;
        }

        /* ── TESTIMONIAL / QUOTE ── */
        .quote-section {
            padding: 100px 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .quote-section::before {
            content: '"';
            position: absolute;
            top: -20px; left: 50%;
            transform: translateX(-50%);
            font-family: var(--font-display);
            font-size: 20rem;
            color: rgba(201,147,42,0.04);
            line-height: 1;
            pointer-events: none;
        }
        .quote-text {
            font-family: var(--font-display);
            font-size: clamp(1.4rem, 2.5vw, 2rem);
            font-style: italic;
            color: var(--cream);
            max-width: 800px;
            margin: 0 auto 2rem;
            line-height: 1.5;
            position: relative; z-index: 1;
        }
        .quote-text span { color: var(--gold-lt); }
        .quote-author {
            font-size: 0.85rem;
            color: var(--cream-dim);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ── CTA SECTION ── */
        .cta-section {
            background: var(--dark-2);
            border-top: 1px solid var(--border-dim);
            padding: 100px 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(201,147,42,0.07) 0%, transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }
        .cta-section h2 {
            font-family: var(--font-display);
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            font-weight: 900;
            color: var(--white);
            margin-bottom: 1rem;
            position: relative; z-index: 1;
        }
        .cta-section h2 em { font-style: italic; color: var(--gold-lt); }
        .cta-section p {
            color: var(--cream-dim);
            font-size: 1rem;
            margin-bottom: 2.5rem;
            font-weight: 300;
            position: relative; z-index: 1;
        }
        .cta-btns {
            display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
            position: relative; z-index: 1;
        }

        /* ── FOOTER ── */
        .footer {
            background: var(--black);
            border-top: 1px solid var(--border-dim);
            padding: 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-brand {
            font-family: var(--font-display);
            font-size: 1.2rem;
            color: var(--gold-lt);
            display: flex; align-items: center; gap: 8px;
        }
        .footer p {
            font-size: 0.8rem;
            color: var(--cream-dim);
        }
        .footer-links {
            display: flex; gap: 1.5rem;
        }
        .footer-links a {
            font-size: 0.82rem;
            color: var(--cream-dim);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--gold-lt); }

        /* ── REVEAL ANIMATIONS ── */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .hero-visual { display: none; }
            .features-layout { grid-template-columns: 1fr; gap: 3rem; }
            .steps-grid { grid-template-columns: 1fr 1fr; }
            .step:nth-child(2) { border-right: none; }
            .step:nth-child(2)::after { display: none; }
        }
        @media (max-width: 640px) {
            .nav { padding: 1rem 1.5rem; }
            .nav-links-top { display: none; }
            .hero { padding: 100px 1.5rem 60px; }
            .section { padding: 70px 1.5rem; }
            .steps-grid { grid-template-columns: 1fr; }
            .step { border-right: none; border-bottom: 1px solid var(--border-dim); }
            .step::after { display: none; }
            .features-grid { grid-template-columns: 1fr; }
            .footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- Custom cursor -->
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- ── NAVBAR ── -->
<nav class="nav">
    <a href="#" class="nav-logo">
        <span class="icon">🐄</span>
        DairyFlow
    </a>
    <ul class="nav-links-top">
        <li><a href="#features">Features</a></li>
        <li><a href="#how-it-works">How It Works</a></li>
        <li><a href="#about">About</a></li>
    </ul>
    <a href="index.php" class="nav-cta">Enter Dashboard →</a>
</nav>

<!-- ── HERO ── -->
<section class="hero" id="home">
    <div class="hero-grid"></div>
    <div class="hero-glow"></div>
    <div class="hero-glow-2"></div>

    <div class="hero-content">
        <div class="hero-eyebrow">
            <span class="dot"></span>
            Dairy Cooperative Management
        </div>
        <h1>
            From <em>Farm</em><br>
            to Future —<br>
            Managed.
        </h1>
        <p class="hero-desc">
            DairyFlow is a complete dairy management system built for cooperatives in India.
            Track farmers, log daily milk production, manage products and generate
            accurate payment bills — all in one place, offline.
        </p>
        <div class="hero-actions">
            <a href="index.php" class="btn-hero-primary">
                Enter Dashboard
                <span class="arrow">→</span>
            </a>
            <a href="#features" class="btn-hero-ghost">
                Explore Features
            </a>
        </div>
    </div>

    <div class="hero-visual">
        <div class="cow-circle">
            🐄
            <div class="cow-orbit">
                <div class="cow-orbit-dot"></div>
            </div>
        </div>
    </div>

    <div class="scroll-hint">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</section>

<!-- ── STATS ── -->
<div class="stats-strip">
    <div class="stat-box reveal">
        <span class="stat-num">∞</span>
        <span class="stat-lbl">Farmers Supported</span>
    </div>
    <div class="stat-box reveal">
        <span class="stat-num">5</span>
        <span class="stat-lbl">Core Modules</span>
    </div>
    <div class="stat-box reveal">
        <span class="stat-num">100%</span>
        <span class="stat-lbl">Offline Ready</span>
    </div>
    <div class="stat-box reveal">
        <span class="stat-num">0₹</span>
        <span class="stat-lbl">Subscription Cost</span>
    </div>
    <div class="stat-box reveal">
        <span class="stat-num">1-Click</span>
        <span class="stat-lbl">Bill Generation</span>
    </div>
</div>

<!-- ── FEATURES ── -->
<section class="section" id="features">
    <div class="features-layout">
        <div class="reveal">
            <div class="section-label">What We Offer</div>
            <h2 class="section-title">Everything your dairy<br><em>cooperative needs</em></h2>
            <p class="section-sub">From registering farmers to generating monthly payment bills — DairyFlow handles every step of your dairy cooperative's workflow with precision.</p>
            <br><br>
            <a href="index.php" class="btn-hero-primary" style="width:fit-content;">
                Open Dashboard →
            </a>
        </div>
        <div class="features-grid reveal">
            <div class="feature-cell">
                <span class="feature-icon">👨‍🌾</span>
                <h3>Farmer Registry</h3>
                <p>Complete farmer profiles with bank details, village info and milk supply history.</p>
            </div>
            <div class="feature-cell">
                <span class="feature-icon">🥛</span>
                <h3>Daily Production Log</h3>
                <p>One record per farmer per day — morning + evening quantities with fat% and SNF%.</p>
            </div>
            <div class="feature-cell">
                <span class="feature-icon">🧀</span>
                <h3>Product Catalogue</h3>
                <p>Manage milk, butter, ghee, paneer and more. Stock auto-updates with production.</p>
            </div>
            <div class="feature-cell">
                <span class="feature-icon">🧾</span>
                <h3>Bill Generator</h3>
                <p>Monthly farmer bills with itemised breakdown, deductions and one-click printing.</p>
            </div>
            <div class="feature-cell">
                <span class="feature-icon">👷</span>
                <h3>Employee Records</h3>
                <p>Track all staff roles, salaries and status across your entire dairy operation.</p>
            </div>
            <div class="feature-cell">
                <span class="feature-icon">🔐</span>
                <h3>Admin Protection</h3>
                <p>Role-based access — guests view, only admin can add, edit or delete any data.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="section steps-section" id="how-it-works">
    <div class="steps-inner">
        <div class="reveal" style="text-align:center;">
            <div class="section-label" style="justify-content:center;">Simple Workflow</div>
            <h2 class="section-title" style="text-align:center;">How <em>DairyFlow</em> works</h2>
        </div>
        <div class="steps-grid">
            <div class="step reveal">
                <span class="step-number">01</span>
                <span class="step-icon">👨‍🌾</span>
                <h3>Register Farmers</h3>
                <p>Add all milk-supplying farmers with their contact info and bank account details for payments.</p>
            </div>
            <div class="step reveal">
                <span class="step-number">02</span>
                <span class="step-icon">🥛</span>
                <h3>Log Daily Milk</h3>
                <p>Each day, enter morning and evening milk quantities per farmer. Stock updates automatically.</p>
            </div>
            <div class="step reveal">
                <span class="step-number">03</span>
                <span class="step-icon">📊</span>
                <h3>Track Products</h3>
                <p>Product stock levels reflect real production data — always up to date without manual entry.</p>
            </div>
            <div class="step reveal">
                <span class="step-number">04</span>
                <span class="step-icon">🧾</span>
                <h3>Generate Bills</h3>
                <p>Select any farmer and month to instantly generate a detailed payment bill ready to print.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── QUOTE ── -->
<section class="quote-section" id="about">
    <p class="quote-text reveal">
        "Built for the <span>farmers of Karnataka</span> — because the backbone of Indian dairy
        deserves tools as reliable as their daily dedication."
    </p>
    <p class="quote-author reveal">DairyFlow — Made for Indian Cooperatives</p>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
    <h2 class="reveal">Ready to manage your<br><em>dairy smarter?</em></h2>
    <p class="reveal">Login as admin or explore the dashboard in view-only mode.</p>
    <div class="cta-btns reveal">
        <a href="index.php" class="btn-hero-primary">
            Enter Dashboard →
        </a>
        <a href="login.php" class="btn-hero-ghost">
            🔐 Admin Login
        </a>
    </div>
</section>

<!-- ── FOOTER ── -->
<footer class="footer">
    <div class="footer-brand">
        <span>🐄</span> DairyFlow
    </div>
    <p>&copy; <?php echo date('Y'); ?> DairyFlow Dairy Management System. Built for XAMPP.</p>
    <div class="footer-links">
        <a href="index.php">Dashboard</a>
        <a href="pages/farmers.php">Farmers</a>
        <a href="pages/bills.php">Bills</a>
        <a href="login.php">Admin</a>
    </div>
</footer>

<script>
    // ── Custom cursor ──
    const cursor     = document.getElementById('cursor');
    const cursorRing = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
        cursor.style.left     = e.clientX + 'px';
        cursor.style.top      = e.clientY + 'px';
        cursorRing.style.left = e.clientX + 'px';
        cursorRing.style.top  = e.clientY + 'px';
    });
    document.querySelectorAll('a, button').forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.style.transform = 'translate(-50%,-50%) scale(2.5)';
            cursor.style.opacity   = '0.6';
        });
        el.addEventListener('mouseleave', () => {
            cursor.style.transform = 'translate(-50%,-50%) scale(1)';
            cursor.style.opacity   = '1';
        });
    });

    // ── Scroll reveal ──
    const revealEls = document.querySelectorAll('.reveal');
    const observer  = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    revealEls.forEach(el => observer.observe(el));

    // ── Smooth anchor scroll ──
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });

    // ── Navbar shrink on scroll ──
    const nav = document.querySelector('.nav');
    window.addEventListener('scroll', () => {
        nav.style.padding = window.scrollY > 60 ? '0.8rem 3rem' : '1.4rem 3rem';
        nav.style.transition = 'padding 0.3s ease';
    });
</script>
</body>
</html>