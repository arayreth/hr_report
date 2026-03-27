<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ./app/controllers/dashboard.php');
} else {
    header('Location: ./app/controllers/login.php');
}
exit;
?>