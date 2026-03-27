<?php
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

// Recherche par code de suivi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if ($code === '') {
        $error = 'Veuillez entrer un code de suivi.';
    } else {
        $stmt = $pdo->prepare("SELECT IdSignalement FROM signalement WHERE CodeSuivi = :code");
        $stmt->execute([':code' => $code]);

        if ($stmt->fetch()) {
            header('Location: suivi.php?code=' . urlencode($code));
            exit;
        } else {
            $error = 'Aucun signalement trouvé pour ce code.';
        }
    }
}

// Récupère tous les signalements de l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT s.IdSignalement, s.CodeSuivi, s.DateCrea, s.Titre, s.Anonyme,
           s.Nom, s.Prenom, s.Pj,
           st.Status AS StatusLibelle,
           c.Libelle AS CategorieLibelle
    FROM signalement s
    LEFT JOIN status st ON s.Status = st.IdStatus
    LEFT JOIN categorie_signalement c ON s.Categorie = c.IdCat
    WHERE s.IdUtilisateur = :userId
    ORDER BY s.DateCrea DESC
");
$stmt->execute([':userId' => $_SESSION['user_id']]);
$signalements = $stmt->fetchAll();

// Stats
$total      = count($signalements);
$en_attente = 0;
$en_cours   = 0;
$ferme      = 0;
foreach ($signalements as $s) {
    if ($s['StatusLibelle'] === 'En attente') $en_attente++;
    if ($s['StatusLibelle'] === 'En cours')   $en_cours++;
    if ($s['StatusLibelle'] === 'Fermé')      $ferme++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon espace</title>
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="dashboard-wrapper">

    <div class="header-row">
        <div class="header-block" style="text-align:left; margin-bottom:0;">
            <div class="badge">Espace déclarant</div>
            <h1 style="margin-top:10px;">Mes signalements</h1>
        </div>
        <a href="logout.php" class="logout-link">Se déconnecter →</a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#e0b432;"><?= $en_attente ?></div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#5ab4f0;"><?= $en_cours ?></div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color:#6cc87a;"><?= $ferme ?></div>
            <div class="stat-label">Fermés</div>
        </div>
    </div>

    <!-- Actions : nouveau signalement + recherche par code -->
    <div class="actions-row">
        <a href="harassment_report_form.php" class="btn-new-report" style="flex:none; width:auto; padding: 12px 24px;">
            ＋ Nouveau signalement
        </a>

        <form method="POST" class="search-form">
            <input
                type="text"
                name="code"
                placeholder="Rechercher par code de suivi…"
                value="<?= htmlspecialchars($_POST['code'] ?? '') ?>"
                autocomplete="off"
            >
            <button type="submit">Rechercher →</button>
        </form>
    </div>

    <?php if ($error): ?>
        <p class="error-inline">⚠️ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Table des signalements -->
    <div class="table-wrapper">
        <?php if (empty($signalements)): ?>
            <div class="empty-state">
                Vous n'avez encore déposé aucun signalement.<br>
                <a href="harassment_report_form.php" style="color:#e07b6a; text-decoration:none; margin-top:10px; display:inline-block;">Déposer un signalement →</a>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Code suivi</th>
                    <th>Catégorie</th>
                    <th>Titre</th>
                    <th>Statut</th>
                    <th>PJ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signalements as $s): ?>
                <?php
                    $cls = match($s['StatusLibelle']) {
                        'En attente' => 'en-attente',
                        'En cours'   => 'en-cours',
                        'Fermé'      => 'ferme',
                        default      => ''
                    };
                ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($s['DateCrea'])) ?></td>
                    <td><span class="code-suivi"><?= htmlspecialchars(substr($s['CodeSuivi'], 0, 8)) ?>…</span></td>
                    <td><?= htmlspecialchars($s['CategorieLibelle']) ?></td>
                    <td><?= htmlspecialchars($s['Titre']) ?></td>
                    <td><span class="badge-status <?= $cls ?>"><?= htmlspecialchars($s['StatusLibelle']) ?></span></td>
                    <td>
                        <?php if ($s['Pj']): ?>
                            <span style="color:#e07b6a;">📎</span>
                        <?php else: ?>
                            <span style="color:rgba(255,255,255,0.2);">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="suivi.php?code=<?= urlencode($s['CodeSuivi']) ?>" class="btn-detail">Voir →</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
