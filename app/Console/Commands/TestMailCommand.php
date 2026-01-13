<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Exception;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email}';

    protected $description = 'Wysyła szybki testowy e-mail przez SMTP Seohost';

    public function handle()
    {
        $targetEmail = $this->argument('email');
        $this->info("Próba wysłania maila do: {$targetEmail}...");

        try {
            Mail::raw('Jeśli to widzisz, Twoje SMTP na Seohost działa poprawnie!', function ($message) use ($targetEmail) {
                $message->to($targetEmail)
                    ->subject('Testowy mail');
            });

            $this->info('Sukces! Mail został wysłany.');
        } catch (Exception $e) {
            $this->error('Błąd wysyłki: ' . $e->getMessage());
            $this->line('Sprawdź swoje zmienne MAIL_ w panelu Laravel Cloud.');
        }
    }
}
