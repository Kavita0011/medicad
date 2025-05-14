<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log the payload
        Log::info('GitHub Webhook:', $request->all());

        // Example: run git pull on push
        if ($request->header('X-GitHub-Event') === 'push') {
            shell_exec('cd /var/www/laravel && git pull 2>&1');
        }

        return response()->json(['status' => 'Webhook received']);
    }
}
// Compare this snippet from app/Http/Controllers/GitHubWebhookController.php:

?>