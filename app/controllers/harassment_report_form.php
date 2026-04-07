<?php
session_start();
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/logger.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    write_log('warning', 'FORM_ACCES_NON_AUTORISE');
    header('Location: login.php');
    exit;
}

$errors = [];

$reporting_types = [
    'moral_harassment'          => 'Harcèlement moral',
    'sexual_harassment'         => 'Harcèlement sexuel',
    'discriminatory_harassment' => 'Harcèlement discriminatoire',
    'abuse_of_authority'        => 'Abus d\'autorité',
    'other'                     => 'Autre',
];

$old = [
    'last_name'      => '',
    'first_name'     => '',
    'anonymous'      => false,
    'reporting_type' => '',
    'title'          => '',
    'description'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['last_name']      = trim($_POST['last_name'] ?? '');
    $old['first_name']     = trim($_POST['first_name'] ?? '');
    $old['anonymous']      = isset($_POST['anonymous']);
    $old['reporting_type'] = $_POST['reporting_type'] ?? '';
    $old['title']          = trim($_POST['title'] ?? '');
    $old['description']    = trim($_POST['description'] ?? '');

    if (!$old['anonymous'] && $old['last_name'] === '' && $old['first_name'] === '') {
        $errors[] = 'Veuillez renseigner votre nom et prénom, ou cocher "Signalement anonyme".';
    }

    if (!array_key_exists($old['reporting_type'], $reporting_types)) {
        $errors[] = 'Veuillez choisir un type de signalement valide.';
    }

    if ($old['title'] === '') {
        $errors[] = 'Le titre est obligatoire.';
    }

    if ($old['description'] === '') {
        $errors[] = 'La description est obligatoire.';
    }

    $uploaded_file = null;
    $pj_filename   = null;

    if (!empty($_FILES['media_proof']['name'])) {

        $allowed_mime = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'application/pdf',
        ];

        $max_size = 2 * 1024 * 1024 * 1024;

        if ($_FILES['media_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload.';
            write_log('warning', 'UPLOAD_ERREUR', ['user_id' => $_SESSION['user_id'], 'error_code' => $_FILES['media_proof']['error']]);
        } elseif ($_FILES['media_proof']['size'] > $max_size) {
            $errors[] = 'Fichier trop volumineux (max 2 Go).';
            write_log('warning', 'UPLOAD_REFUSE', ['user_id' => $_SESSION['user_id'], 'raison' => 'trop_volumineux']);
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $_FILES['media_proof']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed_mime)) {
                $errors[] = 'Format de fichier non autorisé.';
                write_log('warning', 'UPLOAD_REFUSE', ['user_id' => $_SESSION['user_id'], 'raison' => 'mime_invalide', 'mime' => $mime]);
            } else {
                $uploaded_file = $_FILES['media_proof'];
            }
        }
    }

    // Vérification taille totale du dossier upload (max 4 Go)
    $upload_dir = __DIR__ . '/../../public/upload/';
    $total_size = 0;
    foreach (glob($upload_dir . '*') as $file) {
        $total_size += filesize($file);
    }
    if ($total_size >= 4 * 1024 * 1024 * 1024) {
        $errors[] = 'Espace de stockage total atteint (max 4 Go). Contactez l\'administrateur.';
        write_log('warning', 'UPLOAD_STOCKAGE_PLEIN', ['user_id' => $_SESSION['user_id'], 'total_go' => round($total_size / 1024 / 1024 / 1024, 2)]);
    }

    if (!empty($errors)) {
        write_log('warning', 'SIGNALEMENT_ERREUR_VALIDATION', ['user_id' => $_SESSION['user_id'], 'nb_errors' => count($errors)]);
    }

    if (empty($errors)) {

        $codeSuivi = bin2hex(random_bytes(8));

        $categories_map = [
            'moral_harassment'          => 1,
            'sexual_harassment'         => 2,
            'discriminatory_harassment' => 3,
            'abuse_of_authority'        => 4,
            'other'                     => 5,
        ];

        $categorie_id = $categories_map[$old['reporting_type']] ?? null;

        if ($uploaded_file !== null) {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $extension   = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);
            $pj_filename = uniqid('proof_', true) . '.' . $extension;
            $destination = $upload_dir . $pj_filename;

            move_uploaded_file($uploaded_file['tmp_name'], $destination);
            write_log('info', 'UPLOAD_OK', ['user_id' => $_SESSION['user_id'], 'file' => $pj_filename]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO signalement 
            (CodeSuivi, IdUtilisateur, Status, Nom, Prenom, Anonyme, Categorie, Titre, Description, Pj)
            VALUES
            (:codeSuivi, :idUtilisateur, :status, :nom, :prenom, :anonyme, :categorie, :titre, :description, :pj)
        ");

        $stmt->execute([
            ':codeSuivi'      => $codeSuivi,
            ':idUtilisateur'  => $_SESSION['user_id'],
            ':status'         => 1,
            ':nom'            => $old['anonymous'] ? null : encrypt($old['last_name']),
            ':prenom'         => $old['anonymous'] ? null : encrypt($old['first_name']),
            ':anonyme'        => $old['anonymous'] ? 1 : 0,
            ':categorie'      => $categorie_id,
            ':titre'          => encrypt($old['title']),
            ':description'    => encrypt($old['description']),
            ':pj'             => $pj_filename,
        ]);

        write_log('info', 'SIGNALEMENT_CREE', ['user_id' => $_SESSION['user_id'], 'code' => $codeSuivi, 'anonyme' => $old['anonymous'] ? 'oui' : 'non', 'categorie' => $old['reporting_type']]);

        header('Location: /app/views/success.php?code=' . $codeSuivi);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/brand/logo_hr.ico">
    <script src="/index.js"></script>
    <title>Déposer une plainte</title>
</head>
<body>
<div class="page-wrapper">
    <div class="header-block">
        <img src="/brand/logo_hr.png" alt="HR Compliance Tech" class="logo">
        <div class="badge">Confidentiel</div>
        <h1>Signaler un comportement déplacé</h1>
        <p class="subtitle">Votre signalement est traité en toute confidentialité</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div id="message" class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="report_form">

            <section>
                <h2><span class="step">01</span> Informations personnelles</h2>

                <div class="name-fields">
                    <div class="field">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($old['last_name']) ?>" <?= $old['anonymous'] ? 'disabled' : '' ?>>
                    </div>
                    <div class="field">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($old['first_name']) ?>" <?= $old['anonymous'] ? 'disabled' : '' ?>>
                    </div>
                </div>

                <div class="checkbox-field">
                    <input type="checkbox" id="anonymous" name="anonymous" <?= $old['anonymous'] ? 'checked' : '' ?> onchange="toggleNameFields(this.checked)">
                    <label for="anonymous">Signalement anonyme</label>
                </div>
            </section>

            <section>
                <h2><span class="step">02</span> Détails du signalement</h2>

                <label>Type *</label>
                <select name="reporting_type" required>
                    <option value="" disabled selected>Choisir</option>
                    <?php foreach ($reporting_types as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $old['reporting_type'] === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Titre *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($old['title']) ?>">

                <label>Description *</label>
                <textarea name="description"><?= htmlspecialchars($old['description']) ?></textarea>

                <label>Preuve</label>
                <div class="file-wrapper">
                    <input type="file" id="media_proof" name="media_proof" accept="image/*,video/*,.pdf">
                    <div class="file-label" id="file-label">
                        📎 Glissez un fichier ou cliquez pour parcourir
                    </div>
                </div>
                <div id="file-preview"></div>

            </section>

            <button type="submit">Envoyer</button>

        </div>
    </form>

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