<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Your GitHub webhook secret (replace with your actual secret)
        $secret = '9YiKoIXVqym0kpP4yf5/SyvJDF0UeRdk4+0QRlUw8'; 

        // Get the signature headers
        $signature = $request->header('X-Hub-Signature');
        $signature256 = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();

        // Calculate the hash of the payload using the secret
        $hash = 'sha1=' . hash_hmac('sha1', $payload, $secret);
        $hash256 = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        // Verify the signature for sha1
        if (!hash_equals($hash, $signature) && !hash_equals($hash256, $signature256)) {
            return response('Invalid signature', 403);  // Unauthorized if signature doesn't match
        }

        // Log incoming request for debugging
        Log::info('GitHub Webhook Received:', $request->all());

        // Handle Ping Event (to verify that GitHub is reaching your webhook endpoint)
        if ($request->has('zen')) {
            return response('pong', 200);  // Respond to GitHub's ping
        }

        // Handle Push Event
        if ($request->has('ref')) {
            $ref = $request->input('ref');
            $repository = $request->input('repository.name');
            Log::info("Push Event: $repository - $ref");

            // Trigger deployment asynchronously
            dispatch(function() {
                $this->deployCode();
            });

            return response('Deployment triggered', 200);
        }

        return response('OK', 200);
    }

    private function deployCode()
    {
        // Example of pulling the latest changes from GitHub
        $output = null;
        $resultCode = null;
        exec('git pull origin main', $output, $resultCode);  // Adjust 'main' to your branch

        if ($resultCode === 0) {
            Log::info('Deployment successful.');
        } else {
            Log::error('Deployment failed: ' . implode("\n", $output));
        }
    }
}
// Compare this snippet from app/Http/Controllers/HomeController.php: