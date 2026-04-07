<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/logger.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
        $message_class = 'error';
        write_log('warning', 'LOGIN_CHAMPS_VIDES');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Adresse email invalide.';
        $message_class = 'error';
        write_log('warning', 'LOGIN_EMAIL_INVALIDE');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE Login = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['Password'])) {
            $message = 'Email ou mot de passe incorrect.';
            $message_class = 'error';
            write_log('warning', 'LOGIN_FAILED', ['login' => substr($email, 0, 3) . '***']);
        } else {
            session_start();
            $_SESSION['user_id'] = $user['Id_util'];
            $_SESSION['email'] = $user['Login'];
            write_log('info', 'LOGIN_SUCCESS', ['user_id' => $user['Id_util']]);
            header('Location: ./dashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/../../brand/logo_hr.ico">
    <script src="/index.js"></script>
</head>
<body>
    <div class="page-wrapper">
        <div class="header-block">
            <img src="/brand/logo_hr.png" alt="HR Compliance Tech" class="logo">
            <div class="badge">Espace sécurisé</div>
            <h1>Se connecter</h1>
            <p class="subtitle">Accès réservé aux personnes autorisées</p>
        </div>

        <div class="login-container">
            <form method="POST">
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
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                        >
                        <button type="button" id="toggleBtn" class="toggle-password" onclick="togglePassword()">👁️</button>
                    </div>
                </div>

                <button type="submit">
                    Se connecter
                </button>
            </form>

            <p class="login-help">
                Pas de compte ? Contactez
                <a href="mailto:informatique@hrcompliancetech.com">
                    informatique@hrcompliancetech.com
                </a>
            </p>

            <?php if ($message !== ''): ?>
                <p id="message" class="<?= htmlspecialchars($message_class) ?>">
                    <?= htmlspecialchars($message) ?>
                </p>
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