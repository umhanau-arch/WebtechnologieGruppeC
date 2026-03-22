<?php
// register.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Wenn bereits eingeloggt → weiterleiten
if (isLoggedIn()) {
    header('Location: konfigurator.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $pass2   = $_POST['password2'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');

    // Validierung
    if (empty($name) || empty($email) || empty($pass)) {
        $error = 'Bitte alle Pflichtfelder ausfüllen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte eine gültige E-Mail-Adresse eingeben.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwörter stimmen nicht überein.';
    } elseif (strlen($pass) < 6) {
        $error = 'Passwort muss mindestens 6 Zeichen lang sein.';
    } else {
        // Prüfen ob E-Mail bereits vergeben
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Diese E-Mail-Adresse ist bereits registriert.';
        } else {
            // User speichern
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, address, city, zip) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $address, $city, $zip]);
            $success = 'Registrierung erfolgreich! Du kannst dich jetzt einloggen.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren – BowlCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="auth-logo">🥣 BowlCraft</a>
            <h2>Konto erstellen</h2>
            <p>Erstelle ein Konto um deine Bowls zu speichern</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?>
                <a href="login.php">Jetzt einloggen →</a>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-Mail *</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Passwort *</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Passwort wiederholen *</label>
                    <input type="password" class="form-control" name="password2" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="Straße und Hausnummer">
            </div>
            <div class="row">
                <div class="col-4 mb-3">
                    <label class="form-label">PLZ</label>
                    <input type="text" class="form-control" name="zip" value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>">
                </div>
                <div class="col-8 mb-3">
                    <label class="form-label">Stadt</label>
                    <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100 mt-2">Registrieren</button>
        </form>

        <p class="auth-footer">Bereits registriert? <a href="login.php">Einloggen</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
