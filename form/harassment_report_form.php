<?php
// Initialisation des variables
$message = '';
$message_class = '';
$errors = [];

// Types de signalement autorisés
$reporting_types = [
    'moral_harassment'        => 'Harcèlement moral',
    'sexual_harassment'       => 'Harcèlement sexuel',
    'discriminatory_harassment' => 'Harcèlement discriminatoire',
    'abuse_of_authority'      => 'Abus d\'autorité',
    'other'                   => 'Autre',
];

// Récupération des anciennes valeurs pour ré-affichage après erreur
$old = [
    'last_name'      => '',
    'first_name'     => '',
    'anonymous'      => false,
    'reporting_type' => '',
    'title'          => '',
    'description'    => '',
];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des champs
    $old['last_name']      = trim($_POST['last_name'] ?? '');
    $old['first_name']     = trim($_POST['first_name'] ?? '');
    $old['anonymous']      = isset($_POST['anonymous']);
    $old['reporting_type'] = $_POST['reporting_type'] ?? '';
    $old['title']          = trim($_POST['title'] ?? '');
    $old['description']    = trim($_POST['description'] ?? '');

    // Validation
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

    // Validation du fichier (optionnel)
    $uploaded_file = null;
    if (!empty($_FILES['media_proof']['name'])) {
        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/quicktime', 'application/pdf'];
        $max_size = 20 * 1024 * 1024; // 20 Mo

        if ($_FILES['media_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Une erreur est survenue lors de l\'envoi du fichier.';
        } elseif (!in_array($_FILES['media_proof']['type'], $allowed_mime)) {
            $errors[] = 'Format de fichier non autorisé (image, vidéo ou PDF uniquement).';
        } elseif ($_FILES['media_proof']['size'] > $max_size) {
            $errors[] = 'Le fichier dépasse la taille maximale autorisée (20 Mo).';
        } else {
            $uploaded_file = $_FILES['media_proof'];
        }
    }

    // Si pas d'erreurs
    if (empty($errors)) {
        // TODO : enregistrement en base de données
        // TODO : déplacer le fichier avec move_uploaded_file() si $uploaded_file !== null

        $message = 'Votre signalement a bien été envoyé. Merci.';
        $message_class = 'success';

        // Réinitialisation des champs après succès
        $old = [
            'last_name'      => '',
            'first_name'     => '',
            'anonymous'      => false,
            'reporting_type' => '',
            'title'          => '',
            'description'    => '',
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Déposer une plainte</title>
</head>

<body>

<div class="page-wrapper">
    <div class="header-block">
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
    <?php elseif ($message !== ''): ?>
        <p id="message" class="<?= htmlspecialchars($message_class) ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="report_form">

            <section>
                <h2><span class="step">01</span> Informations personnelles</h2>

                <div class="name-fields">
                    <div class="field">
                        <label for="last_name">Nom</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            placeholder="Nom"
                            value="<?= htmlspecialchars($old['last_name']) ?>"
                            <?= $old['anonymous'] ? 'disabled' : '' ?>
                        >
                    </div>
                    <div class="field">
                        <label for="first_name">Prénom</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            placeholder="Prénom"
                            value="<?= htmlspecialchars($old['first_name']) ?>"
                            <?= $old['anonymous'] ? 'disabled' : '' ?>
                        >
                    </div>
                </div>

                <div class="checkbox-field">
                    <input
                        type="checkbox"
                        id="anonymous"
                        name="anonymous"
                        <?= $old['anonymous'] ? 'checked' : '' ?>
                        onchange="toggleNameFields(this.checked)"
                    >
                    <label for="anonymous">Signalement anonyme</label>
                </div>
            </section>

            <section>
                <h2><span class="step">02</span> Détails du signalement</h2>

                <label for="reporting_type">Type de signalement <span class="required">*</span></label>
                <select id="reporting_type" name="reporting_type" required>
                    <option value="" disabled <?= $old['reporting_type'] === '' ? 'selected' : '' ?> hidden>Choisissez une option</option>
                    <?php foreach ($reporting_types as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $old['reporting_type'] === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="title">Titre <span class="required">*</span></label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    placeholder="Objet du signalement"
                    value="<?= htmlspecialchars($old['title']) ?>"
                >

                <label for="description">Description <span class="required">*</span></label>
                <textarea id="description" name="description" rows="5" required placeholder="Racontez-nous les faits en détails"><?= htmlspecialchars($old['description']) ?></textarea>

                <label for="media_proof">Preuve (image, vidéo, PDF)</label>
                <div class="file-wrapper">
                    <input id="media_proof" name="media_proof" type="file" accept="image/*,video/*,.pdf">
                    <div class="file-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <span>Glissez un fichier ou cliquez pour parcourir</span>
                    </div>
                </div>
            </section>

            <button type="submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Envoyer le signalement
            </button>

        </div>
    </form>
</div>

<script>
    function toggleNameFields(isAnonymous) {
        const last  = document.getElementById('last_name');
        const first = document.getElementById('first_name');
        last.disabled  = isAnonymous;
        first.disabled = isAnonymous;
        if (isAnonymous) {
            last.value  = '';
            first.value = '';
        }
    }
</script>

</body>
</html>