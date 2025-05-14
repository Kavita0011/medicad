<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class GitHubWebhookController extends Controller
{
   public function handle(Request $request)
{
    $signature = $request->header('X-Hub-Signature');
    $signature256 = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    $secret = '9YiKoIXVqym0kpP4yf5/SyvJDF0UeRdk4+0QRlUw8';  // Replace this with your actual secret

    // Check if the signature headers are present
    if (!$signature || !$signature256) {
        Log::error('Missing GitHub signature headers.');
        return response('Signature missing', 400);
    }

    // Verify the signature (SHA1 or SHA256)
    if (!$this->verifySignature($payload, $signature, $secret)) {
        Log::error('Invalid signature.');
        return response('Invalid signature', 400);
    }

    // Handle the event if signature is valid
    // Your webhook logic here (e.g., deploy code)

    return response('OK', 200);
}

private function verifySignature($payload, $signature, $secret)
{
    // Extract the signature type (SHA1 or SHA256) from the header
    $signatureParts = explode('=', $signature);
    if (count($signatureParts) != 2) {
        return false;
    }

    $hashType = $signatureParts[0];
    $githubSignature = $signatureParts[1];

    // Check hash equals for SHA1 or SHA256
    if ($hashType === 'sha1') {
        // Verify the SHA1 signature
        $hash = hash_hmac('sha1', $payload, $secret);
    } elseif ($hashType === 'sha256') {
        // Verify the SHA256 signature
        $hash = hash_hmac('sha256', $payload, $secret);
    } else {
        return false; // Invalid signature type
    }

    // Compare the generated hash with the GitHub signature
    return hash_equals($githubSignature, $hash);
}
}
// Compare this snippet from app/Http/Controllers/HomeController.php: