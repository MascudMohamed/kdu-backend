<?php
/**
 * POST /api/newsletter.php
 * JSON body: { "email": "you@example.com", "source": "homepage" }
 *
 * Stores one row per email (unique). Duplicate email returns ok + friendly message.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = read_json_body();
$emailRaw = Validator::cleanString((string) ($body['email'] ?? ''), 190);
$email = mb_strtolower($emailRaw);
$source = Validator::cleanString((string) ($body['source'] ?? 'homepage'), 80);

if (!Validator::email($email)) {
    Response::json(['ok' => false, 'errors' => ['email' => 'Please enter a valid email.']], 422);
}

$pdo = Database::pdo();

$stmt = $pdo->prepare('SELECT id FROM newsletter_subscribers WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    Response::json([
        'ok' => true,
        'message' => 'You are already subscribed.',
        'duplicate' => true,
    ], 200);
}

$ipRaw = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$ipBin = filter_var($ipRaw, FILTER_VALIDATE_IP) ? inet_pton($ipRaw) : null;

$ins = $pdo->prepare(
    'INSERT INTO newsletter_subscribers (email, source, ip_address, user_agent)
     VALUES (:email, :source, :ip, :ua)'
);
$ins->execute([
    'email' => $email,
    'source' => $source,
    'ip' => $ipBin,
    'ua' => Validator::cleanString((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255),
]);

Response::json([
    'ok' => true,
    'message' => 'Thanks — you are subscribed.',
], 201);
