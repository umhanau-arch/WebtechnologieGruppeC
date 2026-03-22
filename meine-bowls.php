<?php
// meine-bowls.php
require_once 'includes/auth.php';
require_once 'includes/db.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Gespeicherte Konfigurationen laden
$stmt = $pdo->prepare('SELECT * FROM configurations WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$configs = $stmt->fetchAll();

// Für jede Config die Zutaten laden
foreach ($configs as &$config) {
    $stmt2 = $pdo->prepare('SELECT i.name FROM config_items ci JOIN ingredients i ON ci.ingredient_id = i.id WHERE ci.config_id = ?');
    $stmt2->execute([$config['id']]);
    $config['ingredients'] = array_column($stmt2->fetchAll(), 'name');
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Bowls – BowlCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark" id="mainNav" style="position:sticky;top:0;z-index:100">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🥣 BowlCraft</a>
        <div class="d-flex gap-3">
            <a href="konfigurator.php" class="btn btn-primary-custom btn-sm">+ Neue Bowl</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="mb-4" style="font-family:'Playfair Display',serif">Meine gespeicherten Bowls 🥣</h2>

    <?php if (empty($configs)): ?>
        <div class="text-center py-5">
            <div style="font-size:4rem">🥣</div>
            <h4 class="mt-3">Noch keine Bowls gespeichert</h4>
            <p class="text-muted">Erstelle deine erste Bowl und speichere sie!</p>
            <a href="konfigurator.php" class="btn btn-primary-custom mt-2">Bowl erstellen →</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($configs as $config): ?>
            <div class="col-md-6 col-lg-4">
                <div class="saved-bowl-card">
                    <div class="saved-bowl-header">
                        <span class="bowl-icon">🥣</span>
                        <div>
                            <h5><?= htmlspecialchars($config['name']) ?></h5>
                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($config['created_at'])) ?></small>
                        </div>
                    </div>
                    <div class="saved-ingredients">
                        <?php foreach ($config['ingredients'] as $ing): ?>
                            <span class="ingredient-tag"><?= htmlspecialchars($ing) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="saved-bowl-footer">
                        <div class="saved-stats">
                            <span>🔥 <?= $config['total_calories'] ?> kcal</span>
                            <span>💪 <?= $config['total_protein'] ?>g</span>
                        </div>
                        <strong class="saved-price"><?= number_format($config['total_price'], 2, ',', '.') ?> €</strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
