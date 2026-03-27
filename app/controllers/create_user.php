<?php
// fichier de secours pour créer un utilisateur
require __DIR__ . '/../../config/db.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($login) || empty($password) || empty($confirm)) {
        $message = 'Tous les champs sont obligatoires.';
        $message_class = 'error';
    } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email invalide.';
        $message_class = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Le mot de passe doit faire au moins 8 caractères.';
        $message_class = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Les mots de passe ne correspondent pas.';
        $message_class = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT Id_util FROM utilisateurs WHERE Login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        if ($stmt->fetch()) {
            $message = 'Cet email est déjà utilisé.';
            $message_class = 'error';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (Login, Password) VALUES (:login, :password)");
            $stmt->execute([':login' => $login, ':password' => $hash]);
            $message = 'Utilisateur créé avec succès.';
            $message_class = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un utilisateur</title>
</head>
<body>
    <h1>Créer un utilisateur</h1>

    <?php if ($message !== ''): ?>
        <p style="color: <?= $message_class === 'error' ? 'red' : 'green' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label>Email<br>
            <input type="email" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
        </label><br><br>

        <label>Mot de passe<br>
            <input type="password" name="password" required>
        </label><br><br>

        <label>Confirmer le mot de passe<br>
            <input type="password" name="confirm" required>
        </label><br><br>

        <button type="submit">Créer</button>
    </form>
</body>
</html>
