<?php
// ===========================================================
// Sécurité : vérification de session
// TODO : décommenter quand la session sera branché
// session_start();
// if (!isset($_SESSION['user'])) {
//     header('Location: ../login.php');
//     exit;
// }
// $user = $_SESSION['user'];
// ===========================================================

// ===========================================================
// TODO (SUPPRIMER) : utilisateur de démo par défaut
// À remplacer par les données de session ci-dessus
$user = [
    'first_name' => 'Marie',
    'last_name'  => 'Lambert',
    'email'      => 'marie.lambert@entreprise.fr',
    'role'       => 'rh',
];
// ===========================================================

// Référentiels
$ROLES = [
    'salarie'  => 'Salarié',
    'rh'       => 'Responsable RH',
    'juriste'  => 'Juriste',
    'admin'    => 'Administrateur',
];

$STATUTS = [
    'OUVERT'           => 'Ouvert',
    'EN_COURS'         => 'En cours',
    'EN_ATTENTE_INFO'  => 'En attente d\'informations',
    'CLOS_FONDE'       => 'Clôturé – Fondé',
    'CLOS_NON_FONDE'   => 'Clôturé – Non fondé',
];

$TYPES = [
    'moral_harassment'           => 'Harcèlement moral',
    'sexual_harassment'          => 'Harcèlement sexuel',
    'discriminatory_harassment'  => 'Harcèlement discriminatoire',
    'abuse_of_authority'         => 'Abus d\'autorité',
];

// ===========================================================
// TODO (SUPPRIMER) : données de démonstration
// À remplacer par un appel à la base de données
$rapports = [
    'SIG-2025-034' => [
        'type'   => 'moral_harassment',
        'statut' => 'EN_COURS',
        'date'   => '12/03/2025',
        'anonyme' => false,
        'auteur' => 'Marie Lambert',
        'titre'  => 'Pression répétée de la hiérarchie',
        'etapes' => [
            ['label' => 'Signalement déposé',      'date' => '12/03/2025', 'fait' => true],
            ['label' => 'Accusé de réception',     'date' => '12/03/2025', 'fait' => true],
            ['label' => 'Instruction en cours',    'date' => '14/03/2025', 'fait' => false, 'actuel' => true],
            ['label' => 'Audition (si nécessaire)','date' => '',           'fait' => false],
            ['label' => 'Décision et clôture',     'date' => '',           'fait' => false],
        ],
    ],
    'SIG-2025-011' => [
        'type'   => 'abuse_of_authority',
        'statut' => 'CLOS_FONDE',
        'date'   => '15/01/2025',
        'anonyme' => true,
        'auteur' => 'Anonyme',
        'titre'  => 'Comportement intimidant du responsable',
        'etapes' => [
            ['label' => 'Signalement déposé', 'fait' => true],
            ['label' => 'Instruction',         'fait' => true],
            ['label' => 'Clôture – Fondé',    'fait' => true],
        ],
    ],
];
// ===========================================================

$nomComplet = trim($user['first_name'] . ' ' . $user['last_name']);
$role       = $user['role'];

// Signalement actif (non clôturé) de l'utilisateur
$clos       = ['CLOS_FONDE', 'CLOS_NON_FONDE'];
$codeActif  = null;
foreach ($rapports as $code => $r) {
    if ($r['auteur'] === $nomComplet && !in_array($r['statut'], $clos)) {
        $codeActif = $code;
        break;
    }
}

// Comptage des signalements de l'utilisateur
// TODO : remplacer par un COUNT() en base de données
$totalCount = count(array_filter($rapports, fn($r) => $r['auteur'] === $nomComplet));

// Recherche par code
$searchCode   = '';
$searchResult = null;
$searchError  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_code'])) {
    $searchCode = strtoupper(trim($_POST['search_code']));
    if (!preg_match('/^SIG-\d{4}-\d{3,}$/', $searchCode)) {
        $searchError = 'Format invalide. Exemple : SIG-2025-034';
    } else {
        // TODO : remplacer par SELECT … WHERE code = :code
        $searchResult = $rapports[$searchCode] ?? null;
    }
}

// Filtres tableau RH
$filterStatus = $_GET['statut'] ?? '';
$filterType   = $_GET['type']   ?? '';

$isRHView = in_array($role, ['rh', 'juriste', 'admin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portail de signalement interne – HRComplianceTech</title>
</head>
<body>

<header>
    <h1>Portail de signalements internes</h1>
    <p>
        Bonjour <strong><?= htmlspecialchars($nomComplet) ?></strong> —
        <?= htmlspecialchars($user['email']) ?> |
        Rôle : <?= htmlspecialchars($ROLES[$role]) ?>

        <form method="post" action="logout.php" style="display:inline">
            <button type="submit">Se déconnecter</button>
        </form>
    </p>
</header>

<hr>

<main>

    <!-- ===== Profil ===== -->
    <h2>Mon profil</h2>
    <p>Email : <?= htmlspecialchars($user['email']) ?></p>
    <p>Rôle : <?= htmlspecialchars($ROLES[$role]) ?></p>
    <p>Nombre de signalements déposés : <?= $totalCount ?></p>

    <hr>

    <!-- ===== Signalement actif ===== -->
    <h2>Mon signalement actif</h2>
    <?php if ($codeActif): $r = $rapports[$codeActif]; ?>
        <p>Code : <strong><?= htmlspecialchars($codeActif) ?></strong></p>
        <p>Type : <?= htmlspecialchars($TYPES[$r['type']]) ?></p>
        <p>Titre : <?= htmlspecialchars($r['titre']) ?></p>
        <p>Statut : <strong><?= htmlspecialchars($STATUTS[$r['statut']]) ?></strong></p>
        <p>Date : <?= htmlspecialchars($r['date']) ?></p>
        <p>Anonyme : <?= $r['anonyme'] ? 'Oui' : 'Non' ?></p>
    <?php else: ?>
        <p>Aucun signalement actif.</p>
    <?php endif; ?>

    <hr>

    <!-- ===== Avancement ===== -->
    <h2>Avancement du signalement</h2>
    <?php if ($codeActif): $etapes = $r['etapes']; $faites = count(array_filter($etapes, fn($e) => $e['fait'])); $pct = round(($faites / count($etapes)) * 100); ?>
        <ol>
            <?php foreach ($etapes as $e): ?>
                <li>
                    <?php
                        if ($e['fait']) echo '✔';
                        elseif (!empty($e['actuel'])) echo '→ en cours';
                        else echo '○';
                    ?>
                    <?= htmlspecialchars($e['label']) ?>
                    <?= !empty($e['date']) ? ' - ' . htmlspecialchars($e['date']) : '' ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <p>Progression : <?= $pct ?>%</p>
    <?php else: ?>
        <p>Aucune donnée d'avancement.</p>
    <?php endif; ?>

    <hr>

    <!-- ===== Recherche ===== -->
    <h2>Rechercher un signalement</h2>
    <form method="post">
        <label for="search_code">Code du signalement :</label>
        <input type="text" id="search_code" name="search_code" placeholder="Ex : SIG-2025-034" maxlength="20" value="<?= htmlspecialchars($searchCode) ?>">
        <button type="submit">Rechercher</button>
    </form>

    <?php if ($searchError): ?>
        <p><?= htmlspecialchars($searchError) ?></p>
    <?php elseif ($searchCode !== '' && $searchResult === null): ?>
        <p>Aucun signalement trouvé.</p>
    <?php elseif ($searchResult): ?>
        <p><strong><?= htmlspecialchars($searchCode) ?></strong></p>
        <p><?= htmlspecialchars($searchResult['titre']) ?></p>
        <p><?= htmlspecialchars($STATUTS[$searchResult['statut']]) ?></p>
    <?php endif; ?>

    <hr>

    <!-- ===== Actions utilisateur ===== -->
    <h2>Actions disponibles</h2>
    <?php if ($role === 'salarie'): ?>
        <ul>
            <li><a href="form/report.php">Déposer un nouveau signalement</a></li>
            <li><a href="form/messaging.php">Accéder à la messagerie sécurisée</a></li>
        </ul>
    <?php endif; ?>

    <!-- ===== Tableau RH ===== -->
    <?php if ($isRHView): ?>
        <hr>
        <h2>Tableau de bord RH / Juriste</h2>

        <form method="get">
            <label>Statut :
                <select name="statut" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <?php foreach ($STATUTS as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $filterStatus === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Type :
                <select name="type" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <?php foreach ($TYPES as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $filterType === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <noscript><button type="submit">Filtrer</button></noscript>
        </form>

        <?php
        // TODO : remplacer par SELECT … WHERE statut = :statut AND type = :type
        $entrees = array_filter($rapports, function($r) use ($filterStatus, $filterType) {
            return (!$filterStatus || $r['statut'] === $filterStatus)
                && (!$filterType   || $r['type']   === $filterType);
        });
        ?>

        <p><?= count($entrees) ?> signalement(s) affiché(s)</p>

        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Anonyme</th>
                    <th>Messagerie</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entrees as $code => $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($code) ?></td>
                        <td><?= htmlspecialchars($TYPES[$r['type']]) ?></td>
                        <td><?= htmlspecialchars($STATUTS[$r['statut']]) ?></td>
                        <td><?= htmlspecialchars($r['date']) ?></td>
                        <td><?= $r['anonyme'] ? 'Oui' : 'Non' ?></td>
                        <td><a href="form/messaging.php?code=<?= urlencode($code) ?>">Messagerie</a></td>
                        <!-- TODO : remplacer par le vrai lien vers la page dossier -->
                        <td><a href="dashboard/case.php?id=<?= urlencode($code) ?>">Ouvrir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>

        <!-- ===== Journal de session ===== -->
        <h2>Journal de session</h2>
        <p><em>Le journal sera alimenté côté serveur une fois la session branchée.</em></p>
        <!-- TODO : afficher ici les entrées du journal stockées en session ou en BDD -->

    <?php endif; ?>

</main>

</body>
</html>