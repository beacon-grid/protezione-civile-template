<?php
http_response_code(401);

// Gestione fallback se il file config o le chiavi non esistono
require_once dirname(__DIR__) . '/php/config.php';
$config = app_config(false);
$site = $config['site'] ?? [
    'meta_title' => 'Sito Istituzionale',
    'nome_breve' => 'Home',
    'address' => ['city' => 'Città']
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticazione richiesta — <?= htmlspecialchars($site['meta_title'], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">
    
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --pc-blue:       #1a3a5c;
            --pc-blue-hover: #11263d;
            --pc-yellow:     #f5c400;
            --pc-gray-bg:    #f4f6f9;
            --transition-smooth: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--pc-gray-bg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .top-bar {
            background: var(--pc-blue);
            padding: .75rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            z-index: 10;
        }

        .top-bar a {
            color: var(--pc-yellow);
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            transition: var(--transition-smooth);
        }

        .top-bar a:hover, .top-bar a:focus-visible {
            color: #fff;
            transform: translateX(2px);
            outline: none;
        }

        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            animation: fadeIn 0.6s ease-out;
        }

        /* Card 401 Modernizzata */
        .card-error {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(26, 58, 92, 0.08), 0 5px 15px rgba(0, 0, 0, 0.04);
            padding: 3.5rem 2rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            transition: var(--transition-smooth);
            border: 1px solid rgba(26, 58, 92, 0.05);
        }

        @media (min-width: 576px) {
            .card-error { padding: 4rem 3.5rem; }
        }

        .card-error:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(26, 58, 92, 0.12), 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        /* Grafica Animata Chiave d'Accesso (Scudo + Chiave che si muove) */
        .icon-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-shield {
            font-size: 4.5rem;
            color: var(--pc-blue);
            z-index: 2;
            animation: float 4s ease-in-out infinite;
        }

        .auth-rings {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px dashed rgba(26, 58, 92, 0.15);
            border-radius: 50%;
            animation: rotate-clockwise 30s linear infinite;
        }

        .badge-key {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--pc-yellow);
            color: var(--pc-blue);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(245, 196, 0, 0.3);
            z-index: 3;
            animation: key-wiggle 3s ease-in-out infinite;
        }

        .badge-401 {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #6c757d;
            color: white;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 4;
        }

        h1 {
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--pc-blue);
            margin-bottom: .75rem;
            letter-spacing: -0.5px;
        }

        p.sub {
            color: #606870;
            font-size: 0.98rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        /* Bottone Principale Login */
        .btn-home {
            background: var(--pc-blue);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .8rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            box-shadow: 0 6px 16px rgba(26, 58, 92, 0.2);
            transition: var(--transition-smooth);
        }

        .btn-home:hover, .btn-home:focus-visible {
            background: var(--pc-blue-hover);
            color: #fff;
            box-shadow: 0 8px 24px rgba(26, 58, 92, 0.3);
            transform: translateY(-2px);
            outline: none;
        }

        .btn-home:active {
            transform: translateY(1px);
        }

        /* Navigazione Link Secondari */
        .links {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ebedf2;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .5rem 1.5rem;
            font-size: .9rem;
        }

        .links a {
            color: var(--pc-blue);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .6rem;
            border-radius: 8px;
            transition: var(--transition-smooth);
        }

        .links a:hover, .links a:focus-visible {
            background-color: rgba(26, 58, 92, 0.06);
            color: var(--pc-blue-hover);
            outline: none;
        }

        /* Struttura Footer Allineata */
        footer {
            background: var(--pc-blue);
            color: rgba(255,255,255,.6);
            padding: 1.25rem 1.5rem;
            font-size: .85rem;
            border-top: 1px solid rgba(255,255,255,.1);
        }

        .footer-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Animazioni CSS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes rotate-clockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes key-wiggle {
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(-15deg) scale(1.05); }
        }
    </style>
</head>
<body>

<header class="top-bar">
    <a href="/" aria-label="Torna alla homepage di <?= htmlspecialchars($site['nome_breve'], ENT_QUOTES, 'UTF-8') ?>">
        <i class="bi bi-shield-fill" aria-hidden="true"></i>
        <span><?= htmlspecialchars($site['nome_breve'], ENT_QUOTES, 'UTF-8') ?></span>
    </a>
</header>

<main class="hero">
    <article class="card-error">

        <!-- Grafica Controllo Accessi (Anello tratteggiato, scudo e chiave dorata oscillante) -->
        <div class="icon-container" aria-hidden="true">
            <div class="auth-rings"></div>
            <i class="bi bi-shield-lock main-shield"></i>
            <span class="badge-key"><i class="bi bi-key-fill"></i></span>
            <span class="badge-401">401</span>
        </div>

        <h1>Accesso richiesto</h1>
        <p class="sub">
            La sessione potrebbe essere scaduta o è necessaria l'autenticazione.<br>
            Effettua il login con le tue credenziali per accedere a quest'area riservata.
        </p>

        <a href="/admin" class="btn-home">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Accedi all'Area Riservata
        </a>

        <nav class="links" aria-label="Link utili alternativi">
            <a href="/"><i class="bi bi-house-fill" aria-hidden="true"></i> Home</a>
            <a href="/news/"><i class="bi bi-newspaper" aria-hidden="true"></i> Notizie</a>
            <a href="/#numeri-utili"><i class="bi bi-telephone-fill" aria-hidden="true"></i> Emergenze</a>
        </nav>

    </article>
</main>

<footer>
    <div class="container-fluid footer-container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y') ?> — Comune di <?= htmlspecialchars($site['address']['city'] ?? 'Territorio', ENT_QUOTES, 'UTF-8') ?> / Gruppo Protezione Civile
            </div>
            <div class="col-md-6 text-center text-md-end" style="color: rgba(255,255,255,0.4)">
                Sito Istituzionale realizzato su base Volontaria
            </div>
        </div>
    </div>
</footer>

</body>
</html>