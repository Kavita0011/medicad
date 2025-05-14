<?php
// Basic security using a secret token
$secret = '9YiKoIXVqym0kpP4yf5/SyvJDF0UeRdk4+0QRlUw8'; // Set this same token in GitHub webhook settings
$headers = getallheaders();

if (!isset($headers['X-Hub-Signature-256'])) {
    http_response_code(403);
    exit('Missing signature.');
}

$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret, false);

if (!hash_equals($hash, $headers['X-Hub-Signature-256'])) {
    http_response_code(403);
    exit('Invalid signature.');
}

// Do the actual git pull
shell_exec("cd /var/www/laravel && git reset --hard HEAD && git pull 2>&1");
echo "Updated successfully!";
