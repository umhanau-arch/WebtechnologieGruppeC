<?php
// index.php - Landing Page
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BowlCraft – Deine perfekte Bowl</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
<style>
* { color: #000000; }
h1, h2, h3, h4, h5, h6 { color: #000000 !important; font-weight: 700; }
p, span, li { color: #111111; }
.text-muted, .section-subtitle, .hero-subtitle, .step-card p, .preset-card p { color: #333333 !important; }
.text-accent, .stat-number { color: #5a9a20 !important; }
.btn-primary-custom { color: #ffffff !important; background: #7bc832 !important; }
.btn-primary-custom:hover { color: #000000 !important; }
.navbar-brand { color: #5a9a20 !important; }
.nav-link { color: #000000 !important; }
.preset-tag { color: #ffffff !important; }
.hero-badge { color: #5a9a20 !important; }
.stat-label { color: #333333 !important; }
.footer-contact-label { color: #333333 !important; }
.footer-links a { color: #333333 !important; }
.footer-links a:hover { color: #5a9a20 !important; }
.footer-tagline { color: #333333 !important; }
.footer-delivery-note { color: #333333 !important; }
.footer-hours-row span:first-child { color: #333333 !important; }
.footer-bottom { color: #333333 !important; }
.payment-badge { color: #000000 !important; }
.btn-outline-secondary { color: #000000 !important; border-color: #000000 !important; }
.btn-outline-accent { color: #5a9a20 !important; border-color: #5a9a20 !important; }
.step-number { color: #cccccc !important; }
</style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🥣 BowlCraft</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="meine-bowls.php">Meine Bowls</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout (<?= htmlspecialchars(getUsername()) ?>)</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-accent" href="register.php">Registrieren</a></li>
                <?php endif; ?>
                <li class="nav-item ms-2"><a class="btn btn-primary-custom" href="konfigurator.php">Bowl erstellen</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="container h-100">
        <div class="row h-100 align-items-center">
            <div class="col-lg-6" data-animate="fadeInLeft">
                <span class="hero-badge">🌱 Frisch. Gesund. Individuell.</span>
                <h1 class="hero-title">Bau dir deine<br><span class="text-accent">perfekte Bowl.</span></h1>
                <p class="hero-subtitle">Wähle aus über 40 frischen Zutaten und kreiere deine einzigartige Bowl – vollständig nach deinen Wünschen konfiguriert.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="konfigurator.php" class="btn btn-primary-custom btn-lg">Jetzt konfigurieren →</a>
                    <a href="#wie-es-geht" class="btn btn-primary-light btn-lg">Wie es funktioniert</a>
                </div>
                <div class="hero-stats mt-4">
                    <div class="stat-item"><span class="stat-number">40+</span><span class="stat-label">Zutaten</span></div>
                    <div class="stat-item"><span class="stat-number">5</span><span class="stat-label">Schritte</span></div>
                    <div class="stat-item"><span class="stat-number">∞</span><span class="stat-label">Kombinationen</span></div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-animate="fadeInRight">
                <div class="hero-bowl-visual">
                    <div class="bowl-ring ring-1"></div>
                    <div class="bowl-ring ring-2"></div>
                    <div class="bowl-ring ring-3"></div>
                    <div class="bowl-emoji">🥣</div>
                    <div class="floating-ingredient" style="top:10%;left:10%">🥑</div>
                    <div class="floating-ingredient" style="top:15%;right:15%">🍗</div>
                    <div class="floating-ingredient" style="bottom:20%;left:5%">🥦</div>
                    <div class="floating-ingredient" style="bottom:15%;right:10%">🍋</div>
                    <div class="floating-ingredient" style="top:50%;left:0%">🥕</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Wie es funktioniert -->
<section class="section-light" id="wie-es-geht">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">In 5 Schritten zur <span class="text-accent">perfekten Bowl</span></h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4 col-lg">
                <div class="step-card" data-animate="fadeInUp" data-delay="0">
                    <div class="step-number">01</div>
                    <div class="step-icon">🍚</div>
                    <h4>Basis wählen</h4>
                    <p>Starte mit deiner Lieblingsbasis: Reis, Quinoa, Nudeln, Salat oder Süßkartoffel.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="step-card" data-animate="fadeInUp" data-delay="100">
                    <div class="step-number">02</div>
                    <div class="step-icon">🥩</div>
                    <h4>Protein & Gemüse</h4>
                    <p>Wähle aus 7 Proteinen und über 22 Gemüsesorten – vegan, vegetarisch oder mit Fleisch.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="step-card" data-animate="fadeInUp" data-delay="200">
                    <div class="step-number">03</div>
                    <div class="step-icon">🫙</div>
                    <h4>Sauce & Toppings</h4>
                    <p>Wähle deine Lieblingssauce und knackige Toppings für den letzten Schliff.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="step-card" data-animate="fadeInUp" data-delay="300">
                    <div class="step-number">04</div>
                    <div class="step-icon">🧾</div>
                    <h4>Zusammenfassung</h4>
                    <p>Prüfe deine Konfiguration, Preis und Nährwerte – und spare mit Gutscheincodes.</p>
                </div>
            </div>
            <div class="col-md-4 col-lg">
                <div class="step-card" data-animate="fadeInUp" data-delay="400">
                    <div class="step-number">05</div>
                    <div class="step-icon">🚚</div>
                    <h4>Bestellen</h4>
                    <p>Lieferadresse und Zahlungsart angeben – und deine Bowl ist unterwegs!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Preset Bowls -->
<section class="section-dark">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Oder starte mit einem <span class="text-accent">Klassiker</span></h2>
            <p class="section-subtitle">Unsere beliebtesten Bowls als Startpunkt – beliebig anpassbar!</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="preset-card" onclick="window.location.href='konfigurator.php?preset=1'">
                    <div class="preset-tag" style="background:#FF6B35">Beliebt</div>
                    <div class="preset-emoji">🍜</div>
                    <h5>Asia Bowl</h5>
                    <p>Jasminreis, Hähnchen, Edamame, Mango, Teriyaki</p>
                    <span class="btn btn-sm btn-outline-accent">Anpassen →</span>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="preset-card" onclick="window.location.href='konfigurator.php?preset=2'">
                    <div class="preset-tag" style="background:#4CAF50">Vegetarisch</div>
                    <div class="preset-emoji">🫒</div>
                    <h5>Mediterranean Bowl</h5>
                    <p>Quinoa, Falafel, Feta, Oliven, Tahini</p>
                    <span class="btn btn-sm btn-outline-accent">Anpassen →</span>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="preset-card" onclick="window.location.href='konfigurator.php?preset=3'">
                    <div class="preset-tag" style="background:#2196F3">High Protein</div>
                    <div class="preset-emoji">💪</div>
                    <h5>Power Bowl</h5>
                    <p>Quinoa, Lachs, Avocado, Spinat, Zitrone-Kräuter</p>
                    <span class="btn btn-sm btn-outline-accent">Anpassen →</span>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="preset-card" onclick="window.location.href='konfigurator.php?preset=4'">
                    <div class="preset-tag" style="background:#9C27B0">Vegan</div>
                    <div class="preset-emoji">🌱</div>
                    <h5>Vegan Dream</h5>
                    <p>Süßkartoffel, Tofu, Kichererbsen, Mango-Chili</p>
                    <span class="btn btn-sm btn-outline-accent">Anpassen →</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container text-center">
        <h2 class="section-title">Bereit für deine Bowl?</h2>
        <p class="section-subtitle mb-4">Erstelle deine perfekte Bowl in nur 3 Schritten.</p>
        <a href="konfigurator.php" class="btn btn-primary-custom btn-lg">Jetzt Bowl konfigurieren →</a>
    </div>
</section>

<!-- Footer -->
<footer class="footer-pro">
    <div class="container">
        <div class="footer-top">
            <div class="row g-5">

                <!-- Spalte 1: Logo & Kontakt -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">BowlCraft</div>
                    <p class="footer-tagline">Frisch. Gesund. Individuell.<br>Deine perfekte Bowl, ganz nach deinen Wünschen konfiguriert.</p>
                    <div class="footer-contact">
                        <div class="footer-contact-row">
                            <span class="footer-contact-label">Adresse</span>
                            <span>Musterstraße 12, 63450 Hanau</span>
                        </div>
                        <div class="footer-contact-row">
                            <span class="footer-contact-label">Telefon</span>
                            <span><a href="tel:+4961819999">06181 9999</a></span>
                        </div>
                        <div class="footer-contact-row">
                            <span class="footer-contact-label">E-Mail</span>
                            <span><a href="mailto:info@bowlcraft.de">info@bowlcraft.de</a></span>
                        </div>
                    </div>
                </div>

                <!-- Spalte 2: Öffnungszeiten -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Öffnungszeiten</h6>
                    <div class="footer-hours">
                        <div class="footer-hours-row">
                            <span>Mo – Fr</span>
                            <span>11:00 – 21:00</span>
                        </div>
                        <div class="footer-hours-row">
                            <span>Sa – So</span>
                            <span>11:00 – 22:00</span>
                        </div>
                        <div class="footer-hours-row">
                            <span>Feiertage</span>
                            <span>12:00 – 20:00</span>
                        </div>
                    </div>
                    <p class="footer-delivery-note">Lieferung bis 30 Min. vor Schließung</p>
                </div>

                <!-- Spalte 3: Versandinfo -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Versandinfo</h6>
                    <div class="footer-hours">
                        <div class="footer-hours-row">
                            <span>Mindestbestellung</span>
                            <span>15,00 €</span>
                        </div>
                        <div class="footer-hours-row">
                            <span>Lieferkosten</span>
                            <span>2,99 €</span>
                        </div>
                        <div class="footer-hours-row">
                            <span>Kostenlos ab</span>
                            <span>30,00 €</span>
                        </div>
                        <div class="footer-hours-row">
                            <span>Lieferzeit</span>
                            <span>25–45 Min.</span>
                        </div>
                    </div>
                </div>

                <!-- Spalte 4: Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Informationen</h6>
                    <ul class="footer-links">
                        <li><a href="#">Über uns</a></li>
                        <li><a href="#">Liefergebiet</a></li>
                        <li><a href="#">Allergene</a></li>
                        <li><a href="#">Kontakt</a></li>
                        <li><a href="#">Impressum</a></li>
                        <li><a href="#">Datenschutz</a></li>
                        <li><a href="#">AGB</a></li>
                    </ul>
                </div>

                <!-- Spalte 5: Zahlung -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-heading">Zahlung</h6>
                    <div class="footer-payment">
                        <div class="payment-badge">Kreditkarte</div>
                        <div class="payment-badge">PayPal</div>
                        <div class="payment-badge">Bar</div>
                        <div class="payment-badge">Rechnung</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-left">
                &copy; <?= date('Y') ?> BowlCraft – Alle Rechte vorbehalten
            </div>
            <div class="footer-bottom-right">
                * Alle Preise inkl. gesetzl. MwSt. zzgl. Lieferkosten
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>