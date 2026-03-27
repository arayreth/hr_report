<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require __DIR__ . '/../../config/db.php';
$code = trim($_GET['code'] ?? '');
if ($code === '') {
    header('Location: dashboard.php');
    exit;
}
$stmt = $pdo->prepare("
    SELECT s.*,
           st.Status AS StatusLibelle,
           c.Libelle AS CategorieLibelle
    FROM signalement s
    LEFT JOIN status st ON s.Status = st.IdStatus
    LEFT JOIN categorie_signalement c ON s.Categorie = c.IdCat
    WHERE s.CodeSuivi = :code
");
$stmt->execute([':code' => $code]);
$signalement = $stmt->fetch();
if (!$signalement) {
    header('Location: /app/controllers/dashboard.php');
    exit;
}
$cls = match($signalement['StatusLibelle']) {
    'En attente' => 'en-attente',
    'En cours'   => 'en-cours',
    'Fermé'      => 'ferme',
    default      => ''
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail — <?= htmlspecialchars($signalement['Titre']) ?></title>
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="/index.js"></script>
</head>
<body>
<div class="page-wrapper">
    <div class="header-block">
        <div class="badge">Détail du signalement</div>
        <h1><?= htmlspecialchars($signalement['Titre']) ?></h1>
        <p class="subtitle">
            Déposé le <?= date('d/m/Y à H:i', strtotime($signalement['DateCrea'])) ?>
        </p>
    </div>
    <div class="suivi-result">
        <div class="suivi-header">
            <div>
                <div class="suivi-title">Informations du déclarant</div>
                <div class="suivi-meta">
                    <?php if ($signalement['Anonyme']): ?>
                        <span class="badge-anon">Anonyme</span>
                    <?php else: ?>
                        <?= htmlspecialchars($signalement['Prenom'] . ' ' . $signalement['Nom']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <span class="badge-status <?= $cls ?>"><?= htmlspecialchars($signalement['StatusLibelle']) ?></span>
        </div>
        <div class="suivi-grid">
            <div class="suivi-field">
                <div class="suivi-field-label">Catégorie</div>
                <div class="suivi-field-value"><?= htmlspecialchars($signalement['CategorieLibelle']) ?></div>
            </div>
            <div class="suivi-field">
                <div class="suivi-field-label">Code de suivi</div>
                <div class="suivi-field-value" style="display: flex; align-items: center; gap: 10px;">
                    <span id="code-suivi" style="font-family: monospace; font-size: 0.82rem; color: rgba(255,255,255,0.5);">
                        <?= htmlspecialchars($signalement['CodeSuivi']) ?>
                    </span>
                    <button
                        type="button"
                        class="btn-copy"
                        onclick="copyCode()"
                        id="btn-copy"
                        title="Copier le code"
                    >
                        📋 Copier
                    </button>
                </div>
            </div>
            <div class="suivi-field suivi-field--full">
                <div class="suivi-field-label">Description</div>
                <div class="suivi-field-value suivi-description">
                    <?= nl2br(htmlspecialchars($signalement['Description'])) ?>
                </div>
            </div>
            <?php if ($signalement['Pj']): ?>
            <div class="suivi-field suivi-field--full">
                <div class="suivi-field-label">Pièce jointe</div>
                <div class="suivi-field-value">
                    <span class="pj-indicator">📎</span>
                    <span style="font-size: 0.82rem; color: rgba(255,255,255,0.55); margin-left: 6px;">
                        <?= htmlspecialchars($signalement['Pj']) ?>
                    </span>
                    <a href="/app/controllers/download.php?file=<?= urlencode($signalement['Pj']) ?>"
                        class="btn-download"
                        target="_blank"
                    >
                        ⬇️ Télécharger
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <a href="/app/controllers/dashboard.php" class="btn-back">← Retour</a>
</div>

</body>
</html>