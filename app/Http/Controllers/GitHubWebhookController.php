<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;



class GitHubWebhookController extends Controller
{public function handle(Request $request)
{
    $signature = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    $secret = $secret = config('services.github.webhook_secret');
    if (!$secret) {
        Log::error('GitHub webhook secret not configured.');
        return response('Webhook secret not configured', 500);
    }

    if (!$signature) {
        Log::error('Missing GitHub signature header.');
        return response('Signature missing', 403);
    }

    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    if (!hash_equals($expected, $signature)) {
        Log::error('Invalid signature from GitHub.');
        return response('Invalid signature', 403);
    }

    // Continue handling the webhook
    Log::info('Valid GitHub webhook received.');
    // ...
    return response('OK', 200);
}
}