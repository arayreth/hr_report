<?php
require __DIR__ . '/../../config/logger.php';

$code = $_GET['code'] ?? null;

if ($code) {
    write_log('info', 'SIGNALEMENT_SUCCESS_PAGE', ['code' => substr($code, 0, 8) . '…']);
} else {
    write_log('warning', 'SUCCESS_PAGE_SANS_CODE');
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
    <title>Confirmation</title>
</head>

<body>

<div class="page-wrapper">

    <div class="header-block">
        <div class="badge">Confidentiel</div>
        <h1>Signalement envoyé</h1>
    </div>

    <form>
        <div class="report_form">

            <section>
                <h2>Confirmation</h2>

                <p class="success-message">
                    Merci pour votre signalement. Il a été enregistré avec succès.
                </p>

                <div class="success-box">
                    <p class="success-text">Votre code de suivi :</p>
                    <div class="tracking-code">
                        <?= htmlspecialchars($code ?? 'Non disponible') ?>
                    </div>
                </div>

            </section>

            <button type="button" onclick="window.location.href='../../index.php'">
                Retour au dashboard
            </button>

        </div>
    </form>

</div>

</body>
</html>
