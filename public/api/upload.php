<?php
/**
 * POST multipart/form-data: file field name "file"
 *
 * Security: whitelist MIME + extension, size cap, random filename, store outside web root
 * if you serve via a download script. Here files land in storage/uploads — do NOT enable
 * directory listing; serve files through a controlled endpoint in production.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
$app = $dbConfig['app'];
$maxBytes = (int) ($app['UPLOAD_MAX_BYTES'] ?? 2_097_152);
$uploadDir = (string) ($app['UPLOAD_DIR'] ?? dirname(__DIR__, 2) . '/storage/uploads');

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    Response::json(['ok' => false, 'error' => 'No file uploaded'], 400);
}

$f = $_FILES['file'];
if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    Response::json(['ok' => false, 'error' => 'Upload error'], 400);
}
if (($f['size'] ?? 0) > $maxBytes) {
    Response::json(['ok' => false, 'error' => 'File too large'], 413);
}

// Verify real MIME (don't trust browser-provided type).
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($f['tmp_name']) ?: '';
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];
if (!isset($allowed[$mime])) {
    Response::json(['ok' => false, 'error' => 'Unsupported file type'], 415);
}

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    Response::json(['ok' => false, 'error' => 'Server storage error'], 500);
}

$ext = $allowed[$mime];
$basename = bin2hex(random_bytes(16)) . '.' . $ext;
$dest = $uploadDir . DIRECTORY_SEPARATOR . $basename;

if (!move_uploaded_file($f['tmp_name'], $dest)) {
    Response::json(['ok' => false, 'error' => 'Could not store file'], 500);
}

Response::json([
    'ok' => true,
    'file' => $basename,
    'mime' => $mime,
    'message' => 'Upload OK — wire this path into your DB in a real feature.',
], 201);
