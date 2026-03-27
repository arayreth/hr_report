<?php
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Accès refusé.');
}

$filename = basename($_GET['file'] ?? '');

if ($filename === '' || $filename !== basename($filename)) {
    http_response_code(400);
    exit('Fichier invalide.');
}

$stmt = $pdo->prepare("SELECT Pj FROM signalement WHERE Pj = :filename LIMIT 1");
$stmt->execute([':filename' => $filename]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

$filepath = __DIR__ . '/../../public/upload/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('Fichier introuvable sur le serveur.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $filepath);
finfo_close($finfo);

$allowed_mime = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/quicktime',
    'application/pdf',
];

if (!in_array($mime, $allowed_mime)) {
    http_response_code(403);
    exit('Type de fichier non autorisé.');
}

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('X-Content-Type-Options: nosniff');
readfile($filepath);
exit;