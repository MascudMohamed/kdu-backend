<?php
/**
 * POST /api/contact.php
 * Body JSON: { "name", "email", "subject", "message" }
 *
 * Why validate server-side: browsers can bypass frontend checks; never trust the client.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = read_json_body();
$name = Validator::cleanString((string) ($body['name'] ?? ''), 120);
$email = Validator::cleanString((string) ($body['email'] ?? ''), 190);
$subject = Validator::cleanString((string) ($body['subject'] ?? ''), 200);
$message = Validator::cleanString((string) ($body['message'] ?? ''), 5000);

$errors = [];
if (mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your name.';
}
if (!Validator::email($email)) {
    $errors['email'] = 'Please enter a valid email.';
}
if (mb_strlen($message) < 10) {
    $errors['message'] = 'Please enter a longer message.';
}

if ($errors !== []) {
    Response::json(['ok' => false, 'errors' => $errors], 422);
}

$pdo = Database::pdo();
$stmt = $pdo->prepare(
    'INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent)
     VALUES (:name, :email, :subject, :message, :ip, :ua)'
);
$ipRaw = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$ipBin = filter_var($ipRaw, FILTER_VALIDATE_IP) ? inet_pton($ipRaw) : null;

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'ip' => $ipBin,
    'ua' => Validator::cleanString((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 255),
]);

Response::json([
    'ok' => true,
    'message' => 'Thank you — your message was received.',
], 201);
