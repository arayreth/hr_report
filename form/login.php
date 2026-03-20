<?php
// Initialisation des variables
$message = '';
$message_class = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
        $message_class = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $message_class = 'error';
    } else {
        // TODO : vérification en base de données
        $message = 'Fonctionnalité de connexion à venir.';
        $message_class = 'info';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <div class="header-block">
            <div class="badge">Espace sécurisé</div>
            <h1>Se connecter</h1>
            <p class="subtitle">Accès réservé aux personnes autorisées</p>
        </div>

        <div class="login-container">
            <form id="loginForm" method="POST" action="">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="exemple@domaine.fr"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Se connecter
                </button>
            </form>

            <p class="login-help">Pas de compte ? Contactez <a href="mailto:informatique@hrcompliancetech.com">informatique@hrcompliancetech.com</a></p>

            <?php if ($message !== ''): ?>
                <p id="message" class="<?= htmlspecialchars($message_class) ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php else: ?>
                <p id="message"></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>