<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log incoming request for debugging
        Log::info('GitHub Webhook Received:', $request->all());

        // Handle Ping Event
        if ($request->has('zen')) {
            // This is a ping event from GitHub
            return response('pong', 200);  // Respond to GitHub's ping
        }

        // Handle Push Event
        if ($request->has('ref')) {
            // This is a push event from GitHub
            $ref = $request->input('ref');
            $repository = $request->input('repository.name');
            Log::info("Push Event: $repository - $ref");

            // You can trigger a Git pull or deploy process here
            $this->deployCode();
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
// Compare this snippet from app/Http/Controllers/GitHubWebhookController.php:

?>