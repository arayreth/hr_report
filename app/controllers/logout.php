<?php
session_start();

require __DIR__ . '/../../config/logger.php';
write_log('info', 'LOGOUT', ['user_id' => $_SESSION['user_id'] ?? 'unknown']);

session_unset();
session_destroy();

header('Location: login.php');
exit;
?>