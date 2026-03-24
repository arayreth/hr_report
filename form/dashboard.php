<?php
session_start();
require_once 'db.php';

// ===========================================================
// CONTROLE SESSION
// Si pas connecté → retour login
// ===========================================================
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user      = $_SESSION['user'];
$role      = $user['role'];
$isRH      = in_array($role, ['rh', 'juriste']);
$isJuriste = ($role === 'juriste');

// ===========================================================
// JOURNAL (session)
// TODO : remplacer par INSERT dans table journal en BDD
// ===========================================================
if (!isset($_SESSION['journal'])) $_SESSION['journal'] = [];

function journaliser($action, $detail = '') {
    $_SESSION['journal'][] = [
        'date'   => date('d/m/Y H:i'),
        'action' => $action,
        'detail' => $detail
    ];
}

// ===========================================================
// REFERENTIELS (BDD)
// ===========================================================
$categories = $pdo->query("SELECT * FROM Categorie_Signalement")->fetchAll();
$statuts    = $pdo->query("SELECT * FROM Status")->fetchAll();

// ===========================================================
// IA SIMPLE
// ===========================================================
$IA = [
    'harcelement' => ['insulte', 'pression'],
    'sexuel'      => ['sexuel', 'attouchement'],
];

// ===========================================================
// ACTION DEPOT
// ===========================================================
if (($_POST['action'] ?? '') === 'deposer') {

    $titre   = htmlspecialchars($_POST['titre']);
    $desc    = htmlspecialchars($_POST['description']);
    $cat     = (int)$_POST['categorie'];
    $anonyme = isset($_POST['anonyme']);

    // IA : requalification automatique
    foreach ($IA as $label => $mots) {
        foreach ($mots as $m) {
            if (str_contains(strtolower($desc), $m)) {
                $q = $pdo->prepare("SELECT IdCat FROM Categorie_Signalement WHERE Libelle LIKE ?");
                $q->execute(["%$label%"]);
                if ($id = $q->fetchColumn()) $cat = $id;
            }
        }
    }

    // Utilisateur anonyme
    $idUtil = $user['id_util'];
    if ($anonyme) {
        $pdo->prepare("INSERT INTO Utilisateurs (CodeAnonyme) VALUES (?)")
            ->execute(['ANON-' . uniqid()]);
        $idUtil = $pdo->lastInsertId();
    }

    // INSERT signalement
    $pdo->prepare("
        INSERT INTO Signalement (Titre, Signalement, Categorie, Status)
        VALUES (?,?,?,1)
    ")->execute([$titre, $desc, $cat]);

    $id   = $pdo->lastInsertId();
    $code = 'SIG-' . date('Y') . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);

    $pdo->prepare("UPDATE Signalement SET CodeSuivi=? WHERE IdSignalement=?")
        ->execute([$code, $id]);

    // Pièces jointes → PiecesJointes + Signalement_PJ
    // TODO : stocker les fichiers dans un répertoire sécurisé hors webroot
    if (!empty($_FILES['pieces']['name'][0])) {
        $typesOk = ['application/pdf', 'image/jpeg', 'image/png', 'audio/mpeg', 'audio/wav'];
        $stmtPj  = $pdo->prepare("INSERT INTO PiecesJointes (Pj) VALUES (?)");
        $stmtSpj = $pdo->prepare("INSERT INTO Signalement_PJ (IdSignalement,IdPj) VALUES (?,?)");
        foreach ($_FILES['pieces']['name'] as $i => $fname) {
            if ($fname && in_array($_FILES['pieces']['type'][$i], $typesOk)) {
                $stmtPj->execute([htmlspecialchars($fname)]);
                $stmtSpj->execute([$id, $pdo->lastInsertId()]);
            }
        }
    }

    // Accusé de réception automatique
    $pdo->prepare("
        INSERT INTO Message (AuteurMessage, DestinataireMessage, ContenuMessage)
        VALUES (?,?,?)
    ")->execute([
        $idUtil,
        $user['id_gest'],
        'Accusé de réception – signalement enregistré sous ' . $code
    ]);

    journaliser('DEPOT', $code);
}

// ===========================================================
// ACTION MESSAGE
// ===========================================================
if (($_POST['action'] ?? '') === 'message') {

    $msg   = htmlspecialchars($_POST['msg']);
    $idSig = (int)$_POST['id_signalement'];

    $pdo->prepare("
        INSERT INTO Message (AuteurMessage, DestinataireMessage, ContenuMessage)
        VALUES (?,?,?)
    ")->execute([
        $user['id_util'],
        $user['id_gest'],
        $msg
    ]);

    journaliser('MESSAGE', '#' . $idSig);
}

// ===========================================================
// ACTION STATUT
// ===========================================================
if (($_POST['action'] ?? '') === 'statut' && $isRH) {

    $pdo->prepare("UPDATE Signalement SET Status=? WHERE IdSignalement=?")
        ->execute([(int)$_POST['statut'], (int)$_POST['id']]);

    journaliser('STATUT', '#' . (int)$_POST['id']);
}

// ===========================================================
// ACTION ANNOTATION
// ===========================================================
if (($_POST['action'] ?? '') === 'annoter' && $isJuriste) {

    $pdo->prepare("UPDATE Signalement SET AnnotationLegal=? WHERE IdSignalement=?")
        ->execute([htmlspecialchars($_POST['annotation']), (int)$_POST['id']]);

    journaliser('ANNOTATION', '#' . (int)$_POST['id']);
}

// ===========================================================
// DECONNEXION
// ===========================================================
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ===========================================================
// ROUTING
// ===========================================================
$page = $_GET['page'] ?? 'home';

// ===========================================================
// DATA (BDD)
// ===========================================================
$rapports = $pdo->query("
    SELECT s.*, c.Libelle cat, st.Status stat
    FROM Signalement s
    LEFT JOIN Categorie_Signalement c ON c.IdCat  = s.Categorie
    LEFT JOIN Status st               ON st.IdStatus = s.Status
    ORDER BY s.DateCrea DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Portail</title>
</head>
<body>

<h1>Portail de signalement</h1>
<p>
    <?= htmlspecialchars($user['nom']) ?>
    (<?= htmlspecialchars($role) ?> – <?= htmlspecialchars($user['email']) ?>)
    | <a href="?action=logout">Déconnexion</a>
</p>

<hr>

<a href="?">Accueil</a> |
<a href="?page=report">Déposer</a> |
<a href="?page=msg">Messagerie</a>

<?php if ($isRH): ?>
| <a href="?page=dashboard">RH</a>
| <a href="?page=archives">Archives</a>
| <a href="?page=journal">Journal</a>
<?php endif; ?>

<?php if ($isJuriste): ?>
| <a href="?page=juriste">Juriste</a>
<?php endif; ?>

<hr>

<!-- ================= ACCUEIL ================= -->
<?php if ($page === 'home'): ?>

<h2>Mes signalements</h2>

<table border="1">
<tr><th>Code</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th>%</th></tr>

<?php foreach ($rapports as $r):
$p = match((int)$r['Status']) {
    1 => 25, 2 => 50, 3 => 75, 4 => 100, default => 0
};
?>
<tr>
<td><?= htmlspecialchars($r['CodeSuivi']) ?></td>
<td><?= htmlspecialchars($r['Titre'])    ?></td>
<td><?= htmlspecialchars($r['cat'])      ?></td>
<td><?= htmlspecialchars($r['stat'])     ?></td>
<td><?= $p ?>%</td>
</tr>
<?php endforeach; ?>

</table>

<!-- ================= DEPOT ================= -->
<?php elseif ($page === 'report'): ?>

<h2>Déposer</h2>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="deposer">

Titre<br>
<input name="titre" required><br>

Catégorie<br>
<select name="categorie">
<?php foreach ($categories as $c): ?>
<option value="<?= $c['IdCat'] ?>"><?= htmlspecialchars($c['Libelle']) ?></option>
<?php endforeach; ?>
</select><br>

Description<br>
<textarea name="description" required></textarea><br>

Pièces jointes (PDF, image, audio)<br>
<input type="file" name="pieces[]" multiple accept=".pdf,.jpg,.jpeg,.png,.mp3,.wav"><br>

<label><input type="checkbox" name="anonyme"> Anonyme</label><br>

<button>Envoyer</button>
</form>

<!-- ================= MESSAGERIE ================= -->
<?php elseif ($page === 'msg'): ?>

<h2>Messagerie</h2>

<form method="get">
<input type="hidden" name="page" value="msg">
<select name="id">
<?php foreach ($rapports as $r): ?>
<option value="<?= (int)$r['IdSignalement'] ?>" <?= ($_GET['id'] ?? 0) == $r['IdSignalement'] ? 'selected' : '' ?>>
    <?= htmlspecialchars($r['CodeSuivi']) ?> – <?= htmlspecialchars($r['Titre']) ?>
</option>
<?php endforeach; ?>
</select>
<button>OK</button>
</form>

<?php
$idSig = (int)($_GET['id'] ?? 0);
if ($idSig):
    $msgs = $pdo->prepare("
        SELECT m.ContenuMessage, m.DateMessage,
               u.CodeAnonyme auteurNom,
               g.Nom gestNom
        FROM Message m
        LEFT JOIN Utilisateurs u ON u.Id_util          = m.AuteurMessage
        LEFT JOIN UtilGest g     ON g.Id_UtilGest       = m.DestinataireMessage
        WHERE m.AuteurMessage = ? OR m.DestinataireMessage = ?
        ORDER BY m.DateMessage ASC
    ");
    $msgs->execute([$user['id_util'], $user['id_gest']]);
?>

<table border="1">
<tr><th>Date</th><th>De</th><th>Message</th></tr>
<?php foreach ($msgs->fetchAll() as $m): ?>
<tr>
<td><?= htmlspecialchars($m['DateMessage'])                          ?></td>
<td><?= htmlspecialchars($m['auteurNom'] ?? $m['gestNom'] ?? '–')    ?></td>
<td><?= htmlspecialchars($m['ContenuMessage'])                       ?></td>
</tr>
<?php endforeach; ?>
</table>

<form method="post">
<input type="hidden" name="action" value="message">
<input type="hidden" name="id_signalement" value="<?= $idSig ?>">
<input name="msg" required>
<button>Envoyer</button>
</form>

<?php endif; ?>

<!-- ================= DASHBOARD RH ================= -->
<?php elseif ($page === 'dashboard' && $isRH): ?>

<h2>Dashboard RH</h2>

<table border="1">
<tr><th>Code</th><th>Titre</th><th>Statut</th><th>Changer statut</th></tr>

<?php foreach ($rapports as $r): ?>
<tr>
<td><?= htmlspecialchars($r['CodeSuivi']) ?></td>
<td><?= htmlspecialchars($r['Titre'])    ?></td>
<td><?= htmlspecialchars($r['stat'])     ?></td>
<td>
<form method="post">
<input type="hidden" name="action" value="statut">
<input type="hidden" name="id" value="<?= (int)$r['IdSignalement'] ?>">
<select name="statut">
<?php foreach ($statuts as $s): ?>
<option value="<?= $s['IdStatus'] ?>" <?= $r['Status'] == $s['IdStatus'] ? 'selected' : '' ?>>
    <?= htmlspecialchars($s['Status']) ?>
</option>
<?php endforeach; ?>
</select>
<button>OK</button>
</form>
</td>
</tr>
<?php endforeach; ?>

</table>

<!-- ================= JURISTE ================= -->
<?php elseif ($page === 'juriste' && $isJuriste): ?>

<h2>Espace juriste</h2>

<?php foreach ($rapports as $r): ?>
<?php if ($r['stat'] !== 'Clos'): ?>
<div style="border:1px solid black;padding:10px;margin:10px;">

<strong><?= htmlspecialchars($r['CodeSuivi']) ?></strong> –
<?= htmlspecialchars($r['Titre']) ?><br>

<?php if (!empty($r['AnnotationLegal'])): ?>
<em>Annotation : <?= htmlspecialchars($r['AnnotationLegal']) ?></em><br>
<?php endif; ?>

<form method="post">
<input type="hidden" name="action" value="annoter">
<input type="hidden" name="id" value="<?= (int)$r['IdSignalement'] ?>">
<input name="annotation" placeholder="Qualification légale…" size="40"
       value="<?= htmlspecialchars($r['AnnotationLegal'] ?? '') ?>">
<button>Annoter</button>
</form>

</div>
<?php endif; endforeach; ?>

<!-- ================= ARCHIVES ================= -->
<?php elseif ($page === 'archives' && $isRH): ?>

<h2>Archives</h2>

<?php
$arch = $pdo->query("
    SELECT s.*, c.Libelle cat, st.Status stat
    FROM Signalement s
    LEFT JOIN Categorie_Signalement c ON c.IdCat    = s.Categorie
    LEFT JOIN Status st               ON st.IdStatus = s.Status
    WHERE st.Status = 'Clos'
    ORDER BY s.DateCrea DESC
")->fetchAll();
?>

<table border="1">
<tr><th>Code</th><th>Titre</th><th>Catégorie</th><th>Annotation</th></tr>

<?php foreach ($arch as $r): ?>
<tr>
<td><?= htmlspecialchars($r['CodeSuivi'])             ?></td>
<td><?= htmlspecialchars($r['Titre'])                 ?></td>
<td><?= htmlspecialchars($r['cat'])                   ?></td>
<td><?= htmlspecialchars($r['AnnotationLegal'] ?? '') ?></td>
</tr>
<?php endforeach; ?>

</table>

<!-- ================= JOURNAL ================= -->
<?php elseif ($page === 'journal' && $isRH): ?>

<h2>Journal</h2>

<table border="1">
<tr><th>Date</th><th>Action</th><th>Détail</th></tr>

<?php foreach ($_SESSION['journal'] as $j): ?>
<tr>
<td><?= htmlspecialchars($j['date'])   ?></td>
<td><?= htmlspecialchars($j['action']) ?></td>
<td><?= htmlspecialchars($j['detail']) ?></td>
</tr>
<?php endforeach; ?>

</table>

<?php endif; ?>

</body>
</html>
