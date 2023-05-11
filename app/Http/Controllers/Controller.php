<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function slackWebhook(Request $request) {
        $eventType = $request->input('type');
        if ($eventType === 'url_verification') {
            return response()->json([
                'challenge' => $request->get('challenge'),
            ]);
        }

        Log::info($eventType);

        if ($eventType !== 'event_callback') {
            return response()->json();
        }

        $eventType = $request->input('event.type');

        Log::info($eventType);

        if ($eventType === 'team_join') {
            $userId = $request->input('event.user.id');
            Log::info("New user: $userId");
            // Send Slack message
            Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.slack.bot_token'),
            ])->post('https://slack.com/api/chat.postMessage', [
                'channel' => $userId,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $this->welcomeMessage(),
                        ],
                    ],
                ],
                'text' => $this->welcomeMessage(),
                'username' => 'Bref bot',
            ])->throw();
            return response()->json();
        }

        return response()->json();
    }

    private function welcomeMessage(): string
    {
        return <<<MD
        *Welcome to the Bref Slack community :tada:*

        We follow these simple rules to keep things great:

        - Be nice and respectful to others
        - Ask questions in the <#C057S6G4Z9N> channel (:warning: *not* in <#CF642J24R>)

        Community support is provided on a best-effort basis. With the amount of questions it is unfortunately impossible to help everyone all the time (I try to help everyone at least once, other volunteers help as well). If you need guaranteed help, consider <https://bref.sh/#ecosystem|Bref's Pro and Enterprise support> (and you'll help Bref become a sustainable open-source project at the same time :heart:).

        Channels you might be interested in:
        - <#C057S6G4Z9N>: community support
        - <#C04AUG4FCTG>: discuss the Laravel integration

        Talk to you soon!
        <@UF6EA2QBA>
        MD;
    }
}
