<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
     $secret = config('services.github.webhook_secret');


        // Get the signature sent by GitHub
        $signature = $request->header('X-Hub-Signature');       // sha1=...
        $signature256 = $request->header('X-Hub-Signature-256'); // sha256=...

        // Get the raw payload for verification
        $payload = $request->getContent();

        // Verify SHA1 signature (optional if you prefer sha256)
        if ($signature && !$this->verifySignature($payload, $signature, $secret, 'sha1')) {
            Log::error('GitHub webhook signature (sha1) mismatch.');
            return response('Invalid signature', 403);
        }

        // Verify SHA256 signature
        if ($signature256 && !$this->verifySignature($payload, $signature256, $secret, 'sha256')) {
            Log::error('GitHub webhook signature (sha256) mismatch.');
            return response('Invalid signature', 403);
        }

        Log::info('GitHub webhook verified.');

        // Handle ping event (contains 'zen')
        if ($request->has('zen')) {
            return response('pong', 200);
        }

        // Handle push event
        if ($request->has('ref')) {
            $ref = $request->input('ref');
            $repository = $request->input('repository.name');
            Log::info("Push event received for repo $repository on ref $ref");

            // Your deployment or processing logic here
            $this->deployCode();

            return response('Push event processed', 200);
        }

        return response('Event received', 200);
    }

    private function verifySignature(string $payload, string $signatureHeader, string $secret, string $algo): bool
    {
        $expectedSignature = $algo . '=' . hash_hmac($algo, $payload, $secret);

        // Use hash_equals to prevent timing attacks
        return hash_equals($expectedSignature, $signatureHeader);
    }

    private function deployCode()
    {
exec('cd /var/www/laravel && git pull origin main 2>&1', $output, $resultCode);

        if ($resultCode === 0) {
            Log::info('Deployment successful: ' . implode("\n", $output));
        } else {
            Log::error('Deployment failed: ' . implode("\n", $output));
        }
    }
}
?>