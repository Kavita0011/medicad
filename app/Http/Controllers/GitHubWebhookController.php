<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class GitHubWebhookController extends Controller
{
  public function handle(Request $request)
{
    $payload = $request->getContent();
    $secret = env('GITHUB_WEBHOOK_SECRET'); // Set in your .env file

    // Prefer SHA256 if available
    $signature = $request->header('X-Hub-Signature-256') 
               ?? $request->header('X-Hub-Signature');

    if (!$signature) {
        Log::error('Signature missing from GitHub webhook.');
        return response('Signature missing', 400);
    }

    if (!$this->isValidSignature($signature, $payload, $secret)) {
        Log::error('Invalid GitHub signature.');
        return response('Invalid signature', 403);
    }

    Log::info('Webhook signature verified.');

    // Your webhook logic here
    return response('OK', 200);
}

private function isValidSignature($signatureHeader, $payload, $secret)
{
    if (str_starts_with($signatureHeader, 'sha256=')) {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    } elseif (str_starts_with($signatureHeader, 'sha1=')) {
        $expected = 'sha1=' . hash_hmac('sha1', $payload, $secret);
    } else {
        return false;
    }

    return hash_equals($expected, $signatureHeader);
}
}