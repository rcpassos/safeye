<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\NotifyCheckIncident;
use App\Mail\NotifyCheckIncident as NotifyCheckIncidentMail;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class NotifyCheckIncidentActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_email_to_single_recipient(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $history = CheckHistory::factory()->create(['check_id' => $check->id]);

        $action = app(NotifyCheckIncident::class);
        $action->handle(['test@example.com'], $history);

        Mail::assertSent(NotifyCheckIncidentMail::class, function ($mail) use ($history) {
            return $mail->hasTo('test@example.com') &&
                   $mail->checkHistory->id === $history->id;
        });
    }

    public function test_sends_email_to_multiple_recipients(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $history = CheckHistory::factory()->create(['check_id' => $check->id]);

        $emails = ['admin@example.com', 'dev@example.com', 'support@example.com'];

        $action = app(NotifyCheckIncident::class);
        $action->handle($emails, $history);

        Mail::assertSent(NotifyCheckIncidentMail::class, function ($mail) use ($emails) {
            foreach ($emails as $email) {
                if (! $mail->hasTo($email)) {
                    return false;
                }
            }

            return true;
        });
    }
}
