<?php
session_start();
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/logger.php';

if (!isset($_SESSION['user_id'])) {
    write_log('warning', 'DASHBOARD_ACCES_NON_AUTORISE');
    header('Location: login.php');
    exit;
}

write_log('info', 'DASHBOARD_ACCES', ['user_id' => $_SESSION['user_id']]);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if ($code === '') {
        $error = 'Veuillez entrer un code de suivi.';
        write_log('warning', 'RECHERCHE_CODE_VIDE', ['user_id' => $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT IdSignalement FROM signalement WHERE CodeSuivi = :code");
        $stmt->execute([':code' => $code]);

        if ($stmt->fetch()) {
            write_log('info', 'RECHERCHE_CODE_TROUVE', ['user_id' => $_SESSION['user_id'], 'code' => substr($code, 0, 8) . '…']);
            header('Location: suivi.php?code=' . urlencode($code));
            exit;
        } else {
            $error = 'Aucun signalement trouvé pour ce code.';
            write_log('warning', 'RECHERCHE_CODE_INTROUVABLE', ['user_id' => $_SESSION['user_id'], 'code' => substr($code, 0, 8) . '…']);
        }
    }
}

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
    <title>Mon espace – Legatech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/brand/logo_hr.ico">
</head>
<body>
<div class="dashboard-wrapper">

    <div class="header-row">
        <img src="/brand/logo_hr.png" alt="HR Compliance Tech" class="logo dashboard-logo">
        <a href="logout.php" class="logout-link">Se déconnecter →</a>
    </div>

    <div class="header-block dashboard-header-block">
        <div class="badge">Espace déclarant</div>
        <h1>Mes signalements</h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number--attente"><?= $en_attente ?></div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number--cours"><?= $en_cours ?></div>
            <div class="stat-label">En cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number--ferme"><?= $ferme ?></div>
            <div class="stat-label">Fermés</div>
        </div>
    </div>

    <div class="actions-row">
        <a href="harassment_report_form.php" class="btn-new-report btn-new-report--inline">
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

    <div class="table-wrapper">
        <?php if (empty($signalements)): ?>
            <div class="empty-state">
                Vous n'avez encore déposé aucun signalement.<br>
                <a href="harassment_report_form.php" class="empty-state-link">Déposer un signalement →</a>
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
                    <td><?= htmlspecialchars(decrypt($s['Titre'])) ?></td>
                    <td><span class="badge-status <?= $cls ?>"><?= htmlspecialchars($s['StatusLibelle']) ?></span></td>
                    <td>
                        <?php if ($s['Pj']): ?>
                            <span class="pj-present">📎</span>
                        <?php else: ?>
                            <span class="pj-absent">—</span>
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

    <footer class="dashboard-footer">
        <p>
            &copy; <?= date('Y') ?> Legatech – Tous droits réservés
            &nbsp;|&nbsp;
            <a href="../legal/sapin2.php">Loi Sapin 2</a>
            &nbsp;|&nbsp;
            <a href="../legal/mention_legales.php">Mentions légales</a>
        </p>
    </footer>

</div>
</body>
</html>
