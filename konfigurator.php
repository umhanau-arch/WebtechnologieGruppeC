<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$presetId = isset($_GET['preset']) ? (int)$_GET['preset'] : 0;
$presetData = [];

if ($presetId > 0) {
    $stmt = $pdo->prepare('SELECT pi.ingredient_id FROM preset_items pi WHERE pi.preset_id = ?');
    $stmt->execute([$presetId]);
    $presetData = array_column($stmt->fetchAll(), 'ingredient_id');
}

$stmt = $pdo->query('SELECT i.*, c.name as category_name, c.step_number, c.max_select, c.icon
                     FROM ingredients i
                     JOIN categories c ON i.category_id = c.id
                     WHERE i.available = 1
                     ORDER BY c.step_number, c.id, i.id');
$allIngredients = $stmt->fetchAll();

$steps = [];
foreach ($allIngredients as $ing) {
    $step = $ing['step_number'];
    $cat  = $ing['category_name'];
    if (!isset($steps[$step])) $steps[$step] = [];
    if (!isset($steps[$step][$cat])) $steps[$step][$cat] = ['ingredients' => [], 'max_select' => $ing['max_select'], 'icon' => $ing['icon']];
    $steps[$step][$cat]['ingredients'][] = $ing;
}

$user = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}

$bestellNr     = null;
$bestellFehler = '';
$bowlItemsJson = '[]';
$bestellTotal  = '0.00';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['liefer_name'])) {
    $bowlItemsJson = $_POST['bowl_items'] ?? '[]';
    $bestellTotal  = $_POST['bowl_total'] ?? '0.00';

    if (!isLoggedIn()) {
        $bestellFehler = 'Bitte einloggen!';
    } else {
        $lieferName    = trim($_POST['liefer_name'] ?? '');
        $lieferAdresse = trim($_POST['liefer_adresse'] ?? '');
        $lieferPLZ     = trim($_POST['liefer_plz'] ?? '');
        $lieferStadt   = trim($_POST['liefer_stadt'] ?? '');
        $lieferZeit    = $_POST['liefer_zeit'] ?? '';
        $zahlungsart   = $_POST['zahlungsart'] ?? '';

        if (empty($lieferName) || empty($lieferAdresse) || empty($lieferPLZ) || empty($lieferStadt)) {
            $bestellFehler = 'Bitte alle Lieferfelder ausfüllen.';
        } elseif (empty($zahlungsart)) {
            $bestellFehler = 'Bitte eine Zahlungsart wählen.';
        } elseif (empty($lieferZeit)) {
            $bestellFehler = 'Bitte eine Lieferzeit wählen.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO bestellungen (user_id, config_id, liefer_name, liefer_adresse, liefer_plz, liefer_stadt, liefer_zeit, zahlungsart, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "ausstehend")');
                $stmt->execute([$_SESSION['user_id'], null, $lieferName, $lieferAdresse, $lieferPLZ, $lieferStadt, $lieferZeit, $zahlungsart]);
                $bestellNr = $pdo->lastInsertId();
            } catch (Exception $e) {
                $bestellFehler = 'Fehler: ' . $e->getMessage();
            }
        }
    }
}

// Bowl Items für Anzeige parsen
$bowlItems = json_decode($bowlItemsJson, true) ?: [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bowl konfigurieren - BowlCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="konfigurator-page">

<nav class="navbar navbar-dark" id="mainNav" style="position:sticky;top:0;z-index:100">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">BowlCraft</a>
        <div class="d-flex align-items-center gap-3">
            <?php if (isLoggedIn()): ?>
                <a href="meine-bowls.php" class="btn btn-sm btn-outline-light">Meine Bowls</a>
                <a href="logout.php" class="nav-link text-light">Logout (<?= htmlspecialchars(getUsername()) ?>)</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-light">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Progress Bar -->
<div class="progress-bar-wrapper">
    <div class="container">
        <div class="step-progress">
            <div class="step-progress-item active" id="prog-1"><div class="step-circle">1</div><span>Basis</span></div>
            <div class="step-progress-line" id="line-1"></div>
            <div class="step-progress-item" id="prog-2"><div class="step-circle">2</div><span>Protein & Gemüse</span></div>
            <div class="step-progress-line" id="line-2"></div>
            <div class="step-progress-item" id="prog-3"><div class="step-circle">3</div><span>Sauce & Toppings</span></div>
            <div class="step-progress-line" id="line-3"></div>
            <div class="step-progress-item" id="prog-4"><div class="step-circle">4</div><span>Zusammenfassung</span></div>
            <div class="step-progress-line" id="line-4"></div>
            <div class="step-progress-item" id="prog-5"><div class="step-circle">5</div><span>Bestellung</span></div>
        </div>
    </div>
</div>

<div class="container konfigurator-container">
    <div class="row">
        <div class="col-lg-8">

            <!-- SCHRITT 1: Basis -->
            <div class="config-step" id="step-1">
                <div class="step-header">
                    <h2>Schritt 1: Wähle deine Basis</h2>
                    <p class="step-desc">Deine Bowl beginnt mit der Basis – wähle genau eine.</p>
                </div>
                <div class="filter-bar mb-3">
                    <button class="filter-btn active" onclick="filterIngredients(1, 'all')">Alle</button>
                    <button class="filter-btn" onclick="filterIngredients(1, 'vegan')">Vegan</button>
                    <button class="filter-btn" onclick="filterIngredients(1, 'glutenfree')">Glutenfrei</button>
                </div>
                <div class="ingredient-grid" id="grid-step-1">
                    <?php foreach ($steps[1] as $catName => $cat): ?>
                        <?php foreach ($cat['ingredients'] as $ing): ?>
                        <div class="ingredient-card <?= $ing['is_vegan'] ? 'is-vegan' : '' ?> <?= $ing['is_glutenfree'] ? 'is-glutenfree' : '' ?>"
                             data-id="<?= $ing['id'] ?>" data-name="<?= htmlspecialchars($ing['name']) ?>"
                             data-price="<?= $ing['price'] ?>" data-cal="<?= $ing['calories'] ?>"
                             data-protein="<?= $ing['protein'] ?>" data-step="1" data-max="1"
                             onclick="toggleIngredient(this)">
                            <div class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></div>
                            <div class="ingredient-price">+<?= number_format($ing['price'], 2) ?> &euro;</div>
                            <div class="ingredient-badges">
                                <?php if ($ing['is_vegan']): ?><span class="badge-vegan">V</span><?php endif; ?>
                                <?php if ($ing['is_glutenfree']): ?><span class="badge-gf">GF</span><?php endif; ?>
                            </div>
                            <div class="ingredient-check">&#10003;</div>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <div class="step-nav">
                    <button class="btn btn-primary-custom" onclick="goToStep(2)">Weiter &rarr;</button>
                </div>
            </div>

            <!-- SCHRITT 2: Protein & Gemüse -->
            <div class="config-step d-none" id="step-2">
                <div class="step-header"><h2>Schritt 2: Protein & Gemüse</h2></div>
                <div class="filter-bar mb-2">
                    <button class="filter-btn active" onclick="filterIngredients(2, 'all')">Alle</button>
                    <button class="filter-btn" onclick="filterIngredients(2, 'vegan')">Vegan</button>
                    <button class="filter-btn" onclick="filterIngredients(2, 'vegetarian')">Vegetarisch</button>
                    <button class="filter-btn" onclick="filterIngredients(2, 'glutenfree')">Glutenfrei</button>
                </div>
                <div class="search-bar mb-3">
                    <input type="text" class="form-control" id="search-step2" placeholder="Zutat suchen..." oninput="searchIngredients(2)">
                </div>
                <?php foreach ($steps[2] as $catName => $cat): ?>
                <div class="category-section">
                    <h5 class="category-title"><?= htmlspecialchars($catName) ?> <small class="text-muted">(max. <?= $cat['max_select'] ?> wählbar)</small></h5>
                    <div class="ingredient-grid">
                        <?php foreach ($cat['ingredients'] as $ing): ?>
                        <div class="ingredient-card <?= $ing['is_vegan'] ? 'is-vegan' : '' ?> <?= $ing['is_vegetarian'] ? 'is-vegetarian' : '' ?> <?= $ing['is_glutenfree'] ? 'is-glutenfree' : '' ?>"
                             data-id="<?= $ing['id'] ?>" data-name="<?= htmlspecialchars($ing['name']) ?>"
                             data-price="<?= $ing['price'] ?>" data-cal="<?= $ing['calories'] ?>"
                             data-protein="<?= $ing['protein'] ?>" data-step="2"
                             data-cat="<?= htmlspecialchars($catName) ?>" data-max="<?= $cat['max_select'] ?>"
                             onclick="toggleIngredient(this)">
                            <div class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></div>
                            <div class="ingredient-price">+<?= number_format($ing['price'], 2) ?> &euro;</div>
                            <div class="ingredient-badges">
                                <?php if ($ing['is_vegan']): ?><span class="badge-vegan">V</span><?php endif; ?>
                                <?php if ($ing['is_glutenfree']): ?><span class="badge-gf">GF</span><?php endif; ?>
                            </div>
                            <div class="ingredient-check">&#10003;</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="step-nav">
                    <button class="btn btn-outline-secondary" onclick="goToStep(1)">&larr; Zurück</button>
                    <button class="btn btn-primary-custom" onclick="goToStep(3)">Weiter &rarr;</button>
                </div>
            </div>

            <!-- SCHRITT 3: Sauce & Toppings -->
            <div class="config-step d-none" id="step-3">
                <div class="step-header"><h2>Schritt 3: Sauce & Toppings</h2></div>
                <div class="filter-bar mb-3">
                    <button class="filter-btn active" onclick="filterIngredients(3, 'all')">Alle</button>
                    <button class="filter-btn" onclick="filterIngredients(3, 'vegan')">Vegan</button>
                    <button class="filter-btn" onclick="filterIngredients(3, 'glutenfree')">Glutenfrei</button>
                </div>
                <?php foreach ($steps[3] as $catName => $cat): ?>
                <div class="category-section">
                    <h5 class="category-title"><?= htmlspecialchars($catName) ?> <small class="text-muted">(max. <?= $cat['max_select'] ?> wählbar)</small></h5>
                    <div class="ingredient-grid">
                        <?php foreach ($cat['ingredients'] as $ing): ?>
                        <div class="ingredient-card <?= $ing['is_vegan'] ? 'is-vegan' : '' ?> <?= $ing['is_glutenfree'] ? 'is-glutenfree' : '' ?>"
                             data-id="<?= $ing['id'] ?>" data-name="<?= htmlspecialchars($ing['name']) ?>"
                             data-price="<?= $ing['price'] ?>" data-cal="<?= $ing['calories'] ?>"
                             data-protein="<?= $ing['protein'] ?>" data-step="3"
                             data-cat="<?= htmlspecialchars($catName) ?>" data-max="<?= $cat['max_select'] ?>"
                             onclick="toggleIngredient(this)">
                            <div class="ingredient-name"><?= htmlspecialchars($ing['name']) ?></div>
                            <div class="ingredient-price">+<?= number_format($ing['price'], 2) ?> &euro;</div>
                            <div class="ingredient-badges">
                                <?php if ($ing['is_vegan']): ?><span class="badge-vegan">V</span><?php endif; ?>
                                <?php if ($ing['is_glutenfree']): ?><span class="badge-gf">GF</span><?php endif; ?>
                            </div>
                            <div class="ingredient-check">&#10003;</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="step-nav">
                    <button class="btn btn-outline-secondary" onclick="goToStep(2)">&larr; Zurück</button>
                    <button class="btn btn-primary-custom" onclick="goToStep(4)">Zur Zusammenfassung &rarr;</button>
                </div>
            </div>

            <!-- SCHRITT 4: Zusammenfassung -->
            <div class="config-step d-none" id="step-4">
                <div class="step-header"><h2>Schritt 4: Zusammenfassung</h2></div>
                <div id="summary-content"></div>
                <div class="coupon-section">
                    <h5>Gutscheincode</h5>
                    <div class="input-group">
                        <input type="text" class="form-control" id="coupon-input" placeholder="Code eingeben (z.B. BOWL10)">
                        <button class="btn btn-outline-secondary" onclick="applyCoupon()">Einlösen</button>
                    </div>
                    <div id="coupon-msg" class="mt-2"></div>
                </div>
                <div class="price-summary">
                    <div class="price-row"><span>Zwischensumme</span><span id="price-subtotal">0,00 &euro;</span></div>
                    <div class="price-row discount-row d-none" id="discount-row">
                        <span id="discount-label">Rabatt</span>
                        <span id="price-discount" class="text-success">-0,00 &euro;</span>
                    </div>
                    <div class="price-row total-row"><span>Gesamtpreis</span><span id="price-total">0,00 &euro;</span></div>
                </div>
                <div class="nutrition-summary">
                    <h5>Nährwerte</h5>
                    <div class="nutrition-grid">
                        <div class="nutrition-item"><span class="nut-val" id="nut-cal">0</span><span class="nut-label">Kalorien</span></div>
                        <div class="nutrition-item"><span class="nut-val" id="nut-protein">0g</span><span class="nut-label">Protein</span></div>
                    </div>
                </div>
                <div class="step-nav flex-column gap-2">
                    <?php if (isLoggedIn()): ?>
                        <div class="mb-2">
                            <input type="text" class="form-control" id="bowl-name" placeholder="Name für deine Bowl (optional)" maxlength="100">
                        </div>
                        <button class="btn btn-outline-accent w-100" onclick="saveConfig()">Bowl speichern</button>
                        <button class="btn btn-primary-custom w-100 btn-lg" onclick="goToStep(5)">Jetzt bestellen &rarr;</button>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <a href="login.php">Einloggen</a> oder <a href="register.php">Registrieren</a> um zu bestellen.
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-outline-secondary" onclick="goToStep(3)">&larr; Zurück</button>
                </div>
                <div id="save-msg" class="mt-3"></div>
            </div>

            <!-- SCHRITT 5: Lieferung & Zahlung -->
            <div class="config-step d-none" id="step-5">
                <div class="step-header">
                    <h2>Schritt 5: Lieferung & Zahlung</h2>
                    <p class="step-desc">Fast geschafft! Gib deine Lieferadresse und Zahlungsart an.</p>
                </div>

                <?php if ($bestellNr): ?>
                <!-- ERFOLG -->
                <div class="bestellung-success">
                    <div class="success-icon">&#127881;</div>
                    <h2>Bestellung erfolgreich!</h2>
                    <p class="text-muted">Bestellnummer: <strong>#<?= str_pad($bestellNr, 5, '0', STR_PAD_LEFT) ?></strong></p>
                    <div class="success-details">
                        <div class="success-row"><span>Status</span><span class="badge-status">In Bearbeitung</span></div>
                        <div class="success-row"><span>Lieferzeit</span><span><?= htmlspecialchars($_POST['liefer_zeit'] ?? '') ?></span></div>
                        <div class="success-row"><span>Adresse</span><span><?= htmlspecialchars(($_POST['liefer_adresse'] ?? '') . ', ' . ($_POST['liefer_plz'] ?? '') . ' ' . ($_POST['liefer_stadt'] ?? '')) ?></span></div>
                        <div class="success-row"><span>Zahlung</span><span><?= htmlspecialchars($_POST['zahlungsart'] ?? '') ?></span></div>
                    </div>
                    <div class="success-timeline">
                        <div class="timeline-step completed"><div class="timeline-dot">&#10003;</div><span>Bestellt</span></div>
                        <div class="timeline-line"></div>
                        <div class="timeline-step active"><div class="timeline-dot">&#9203;</div><span>Wird zubereitet</span></div>
                        <div class="timeline-line"></div>
                        <div class="timeline-step"><div class="timeline-dot">&#128666;</div><span>Unterwegs</span></div>
                        <div class="timeline-line"></div>
                        <div class="timeline-step"><div class="timeline-dot">&#10003;</div><span>Geliefert</span></div>
                    </div>
                    <div class="d-flex gap-3 justify-content-center mt-4">
                        <a href="index.php" class="btn btn-primary-custom">Zur Startseite</a>
                        <a href="meine-bowls.php" class="btn btn-outline-accent">Meine Bowls</a>
                    </div>
                </div>

                <?php else: ?>
                <!-- BESTELLFORMULAR -->
                <?php if ($bestellFehler): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($bestellFehler) ?></div>
                <?php endif; ?>

                <form method="POST" id="bestellform">
                    <input type="hidden" name="bowl_items" id="bowl_items_input" value="">
                    <input type="hidden" name="bowl_total" id="bowl_total_input" value="">

                    <div class="bestellung-section">
                        <h5>Lieferadresse</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Name *</label>
                                <input type="text" class="form-control" name="liefer_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Strasse und Hausnummer *</label>
                                <input type="text" class="form-control" name="liefer_adresse" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label">PLZ *</label>
                                <input type="text" class="form-control" name="liefer_plz" value="<?= htmlspecialchars($user['zip'] ?? '') ?>" required>
                            </div>
                            <div class="col-8">
                                <label class="form-label">Stadt *</label>
                                <input type="text" class="form-control" name="liefer_stadt" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="bestellung-section">
                        <h5>Lieferzeit wählen</h5>
                        <div class="lieferzeit-grid">
                            <label class="lieferzeit-option">
                                <input type="radio" name="liefer_zeit" value="So schnell wie möglich (25-35 Min.)" required>
                                <div class="lieferzeit-card">
                                    <span class="lieferzeit-icon">&#9889;</span>
                                    <span class="lieferzeit-label">So schnell wie möglich</span>
                                    <span class="lieferzeit-time">25-35 Min.</span>
                                </div>
                            </label>
                            <label class="lieferzeit-option">
                                <input type="radio" name="liefer_zeit" value="In 1 Stunde (60 Min.)">
                                <div class="lieferzeit-card">
                                    <span class="lieferzeit-icon">&#128336;</span>
                                    <span class="lieferzeit-label">In 1 Stunde</span>
                                    <span class="lieferzeit-time">60 Min.</span>
                                </div>
                            </label>
                            <label class="lieferzeit-option">
                                <input type="radio" name="liefer_zeit" value="Heute Abend (18:00-20:00 Uhr)">
                                <div class="lieferzeit-card">
                                    <span class="lieferzeit-icon">&#127769;</span>
                                    <span class="lieferzeit-label">Heute Abend</span>
                                    <span class="lieferzeit-time">18:00-20:00 Uhr</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bestellung-section">
                        <h5>Zahlungsart wählen</h5>
                        <div class="zahlung-grid">
                            <label class="zahlung-option">
                                <input type="radio" name="zahlungsart" value="Kreditkarte" required>
                                <div class="zahlung-card">
                                    <span class="zahlung-icon">&#128179;</span>
                                    <span>Kreditkarte</span>
                                    <small>Visa, Mastercard</small>
                                </div>
                            </label>
                            <label class="zahlung-option">
                                <input type="radio" name="zahlungsart" value="PayPal">
                                <div class="zahlung-card">
                                    <span class="zahlung-icon">P</span>
                                    <span>PayPal</span>
                                    <small>Schnell & sicher</small>
                                </div>
                            </label>
                            <label class="zahlung-option">
                                <input type="radio" name="zahlungsart" value="Barzahlung bei Lieferung">
                                <div class="zahlung-card">
                                    <span class="zahlung-icon">&#128181;</span>
                                    <span>Bar</span>
                                    <small>Bei Lieferung</small>
                                </div>
                            </label>
                            <label class="zahlung-option">
                                <input type="radio" name="zahlungsart" value="Rechnung">
                                <div class="zahlung-card">
                                    <span class="zahlung-icon">&#128196;</span>
                                    <span>Rechnung</span>
                                    <small>14 Tage</small>
                                </div>
                            </label>
                        </div>

                        <!-- PayPal Box -->
                        <div id="paypal-felder" class="mt-3 d-none">
                            <div class="paypal-box">
                                <div class="text-center mb-3">
                                    <h5 style="color:#003087">PayPal</h5>
                                </div>
                                <div class="paypal-info-row"><span>Empfänger</span><strong>bezahlung@bowlcraft.de</strong></div>
                                <div class="paypal-info-row"><span>Betrag</span><strong id="paypal-betrag">–</strong></div>
                                <div class="paypal-info-row"><span>Verwendungszweck</span><strong>BowlCraft Bestellung</strong></div>
                                <p class="text-center text-muted small mt-3">Nach dem Bestellen öffnet sich PayPal zur Zahlung.</p>
                            </div>
                        </div>

                        <!-- Kreditkarte Felder -->
                        <div id="kreditkarte-felder" class="mt-3 d-none">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Kartennummer</label>
                                    <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" id="karten-nr">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Gültig bis</label>
                                    <input type="text" class="form-control" placeholder="MM/JJ" maxlength="5">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" maxlength="3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 btn-lg mt-2" onclick="saveBowlToForm()">
                        Jetzt verbindlich bestellen
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="goToStep(4)">&larr; Zurück zur Zusammenfassung</button>
                </form>
                <?php endif; ?>
            </div>

        </div>

        <!-- Rechte Seite: Live-Vorschau -->
        <div class="col-lg-4">
            <div class="bowl-preview-panel sticky-top" style="top:120px">
                <h5 class="preview-title">Deine Bowl</h5>
                <div class="bowl-visual-preview">
                    <!-- SVG Bowl Grafik -->
                    <div class="bowl-svg-container">
                        <svg viewBox="0 0 200 160" xmlns="http://www.w3.org/2000/svg" class="bowl-svg">
                            <!-- Schatten -->
                            <ellipse cx="100" cy="148" rx="70" ry="8" fill="rgba(0,0,0,0.08)"/>
                            <!-- Bowl Außen -->
                            <path d="M30 70 Q30 140 100 145 Q170 140 170 70 Z" fill="#e8e0d5" stroke="#c4b8a8" stroke-width="2"/>
                            <!-- Bowl Innen / Zutaten-Bereich -->
                            <path d="M38 70 Q38 133 100 138 Q162 133 162 70 Z" fill="#f5f0e8"/>
                            <!-- Zutaten Layer 1 (Basis) -->
                            <path id="layer-basis" d="M38 100 Q38 133 100 138 Q162 133 162 100 Z" fill="#d4c5a0" opacity="0" class="bowl-layer"/>
                            <!-- Zutaten Layer 2 (Protein) -->
                            <path id="layer-protein" d="M38 95 Q50 115 100 118 Q150 115 162 95 L162 100 Q162 133 100 138 Q38 133 38 100 Z" fill="#c4956a" opacity="0" class="bowl-layer"/>
                            <!-- Zutaten Layer 3 (Gemüse) -->
                            <g id="layer-gemuese" opacity="0" class="bowl-layer">
                                <circle cx="70" cy="105" r="8" fill="#6ab04c"/>
                                <circle cx="90" cy="100" r="6" fill="#f9ca24"/>
                                <circle cx="110" cy="102" r="7" fill="#ff6b6b"/>
                                <circle cx="130" cy="106" r="6" fill="#4CAF50"/>
                                <circle cx="80" cy="115" r="5" fill="#f0932b"/>
                                <circle cx="120" cy="112" r="6" fill="#6ab04c"/>
                            </g>
                            <!-- Sauce Layer -->
                            <g id="layer-sauce" opacity="0" class="bowl-layer">
                                <path d="M55 90 Q100 98 145 90" stroke="#c0392b" stroke-width="3" fill="none" stroke-linecap="round"/>
                                <path d="M60 95 Q100 103 140 95" stroke="#c0392b" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.6"/>
                            </g>
                            <!-- Toppings -->
                            <g id="layer-toppings" opacity="0" class="bowl-layer">
                                <circle cx="75" cy="88" r="2" fill="#c0392b"/>
                                <circle cx="95" cy="85" r="2" fill="#c0392b"/>
                                <circle cx="115" cy="87" r="2" fill="#c0392b"/>
                                <circle cx="85" cy="92" r="1.5" fill="#7f8c8d"/>
                                <circle cx="110" cy="91" r="1.5" fill="#7f8c8d"/>
                                <circle cx="128" cy="89" r="2" fill="#c0392b"/>
                            </g>
                            <!-- Bowl Rand oben -->
                            <ellipse cx="100" cy="70" rx="70" ry="12" fill="#d4c8b8" stroke="#c4b8a8" stroke-width="1.5"/>
                            <ellipse cx="100" cy="70" rx="62" ry="9" fill="#e8e0d5"/>
                            <!-- Glanz-Effekt -->
                            <ellipse cx="85" cy="68" rx="20" ry="4" fill="white" opacity="0.3"/>
                            <!-- Leer-Text -->
                            <text id="bowl-empty-svg" x="100" y="112" text-anchor="middle" fill="#aaa" font-size="10" font-family="sans-serif">Noch leer...</text>
                        </svg>
                        <!-- Schwebende Zutaten-Emojis -->
                        <div class="floating-ingredients-container" id="floating-ingredients"></div>
                    </div>
                </div>
                <div class="selected-list" id="selected-list">
                    <p class="text-muted text-center small">Wähle deine Zutaten</p>
                </div>
                <div class="preview-price">
                    <span>Aktueller Preis:</span>
                    <strong id="live-price">0,00 &euro;</strong>
                </div>
                <div class="preview-nutrition">
                    <span><span id="live-cal">0</span> kcal</span>
                    <span><span id="live-protein">0</span>g Protein</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const PRESET_IDS = <?= json_encode($presetData) ?>;
const IS_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;

<?php if ($bestellNr): ?>
// Bestellung erfolgreich - Schritt 5 anzeigen und Bowl rechts anzeigen
const BOWL_ITEMS = <?= json_encode($bowlItems) ?>;
const BOWL_TOTAL = "<?= htmlspecialchars($bestellTotal) ?>";

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.config-step').forEach(el => el.classList.add('d-none'));
    document.getElementById('step-5').classList.remove('d-none');
    updateProgressBar(5);

    // Bowl rechts anzeigen
    if (BOWL_ITEMS && BOWL_ITEMS.length > 0) {
        const selectedList = document.getElementById('selected-list');
        const livePrice    = document.getElementById('live-price');
        const liveCal      = document.getElementById('live-cal');
        const liveProtein  = document.getElementById('live-protein');

        if (selectedList) {
            selectedList.innerHTML = BOWL_ITEMS.map(i =>
                `<div class="selected-item">
                    <span>${i.name}</span>
                    <span class="selected-price">+${parseFloat(i.price).toFixed(2).replace('.',',')} &euro;</span>
                </div>`
            ).join('');
        }

        if (livePrice) livePrice.textContent = parseFloat(BOWL_TOTAL).toFixed(2).replace('.',',') + ' \u20ac';

        let cal = 0, prot = 0;
        BOWL_ITEMS.forEach(i => { cal += parseInt(i.cal) || 0; prot += parseFloat(i.protein) || 0; });
        if (liveCal) liveCal.textContent = cal;
        if (liveProtein) liveProtein.textContent = prot.toFixed(1);

        // SVG Bowl Layer aktivieren
        const hasBasis   = BOWL_ITEMS.some(i => i.step == 1);
        const hasProtein = BOWL_ITEMS.some(i => i.step == 2 && i.cat === 'Protein');
        const hasGemuese = BOWL_ITEMS.some(i => i.step == 2 && i.cat !== 'Protein');
        const hasSauce   = BOWL_ITEMS.some(i => i.step == 3 && i.cat === 'Sauce');
        const hasToppings= BOWL_ITEMS.some(i => i.step == 3 && i.cat === 'Topping');

        // Mit kleiner Verzögerung für Animation
        setTimeout(() => {
            ['layer-basis', hasBasis],
            ['layer-protein', hasProtein],
            ['layer-gemuese', hasGemuese],
            ['layer-sauce', hasSauce],
            ['layer-toppings', hasToppings]

            const layers = [
                ['layer-basis', hasBasis],
                ['layer-protein', hasProtein],
                ['layer-gemuese', hasGemuese],
                ['layer-sauce', hasSauce],
                ['layer-toppings', hasToppings]
            ];
            layers.forEach(([id, show], idx) => {
                setTimeout(() => {
                    const el = document.getElementById(id);
                    if (el) { el.style.transition = 'opacity 0.5s ease'; el.style.opacity = show ? '1' : '0'; }
                }, idx * 200);
            });

            // Leer-Text ausblenden
            const emptyText = document.getElementById('bowl-empty-svg');
            if (emptyText) emptyText.style.opacity = '0';

            // Schwebende Emojis
            const emojiMap = {'Jasminreis':'🍚','Quinoa':'🌾','Avocado':'🥑','Mais':'🌽','Brokkoli':'🥦','Karotten':'🥕','Lachs':'🐟','Hähnchen':'🍗','Tofu':'🟨','Salat':'🥗'};
            const container = document.getElementById('floating-ingredients');
            if (container) {
                container.innerHTML = BOWL_ITEMS.slice(-3).map((i, idx) => {
                    const emoji = Object.keys(emojiMap).find(k => i.name.includes(k));
                    return `<span class="float-ing-emoji" style="animation-delay:${idx * 0.2}s">${emoji ? emojiMap[emoji] : '●'}</span>`;
                }).join('');
            }
        }, 300);
    }
});

<?php elseif ($bestellFehler): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.config-step').forEach(el => el.classList.add('d-none'));
    document.getElementById('step-5').classList.remove('d-none');
    updateProgressBar(5);
});
<?php endif; ?>

// Formular: Bowl-Daten speichern bevor abgesendet wird
function saveBowlToForm() {
    const items  = Object.values(selected);
    const total  = items.reduce((s, i) => s + i.price, 0);
    const disc   = appliedCoupon ? total * appliedCoupon.percent / 100 : 0;
    document.getElementById('bowl_items_input').value = JSON.stringify(items);
    document.getElementById('bowl_total_input').value = (total - disc).toFixed(2);
}

// Zahlungsart Felder
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[name="zahlungsart"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const kkFelder = document.getElementById('kreditkarte-felder');
            const ppFelder = document.getElementById('paypal-felder');
            if (kkFelder) kkFelder.classList.toggle('d-none', radio.value !== 'Kreditkarte');
            if (ppFelder) ppFelder.classList.toggle('d-none', radio.value !== 'PayPal');
            if (ppFelder && radio.value === 'PayPal') {
                const total = document.getElementById('price-total');
                const pp    = document.getElementById('paypal-betrag');
                if (total && pp) pp.textContent = total.textContent;
            }
        });
    });

    const kartenNr = document.getElementById('karten-nr');
    if (kartenNr) {
        kartenNr.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 16);
            e.target.value = val.replace(/(.{4})/g, '$1 ').trim();
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/konfigurator.js"></script>
</body>
</html>

<?php
function getIngredientEmoji($name) { return ''; }
?>