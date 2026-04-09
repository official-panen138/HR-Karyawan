<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'hr:set-telegram-webhook {--remove : Remove the webhook}';
    protected $description = 'Set or remove the Telegram bot webhook URL';

    public function handle(): int
    {
        $token = config('services.telegram.bot_token');
        if (empty($token)) {
            $this->error('TELEGRAM_BOT_TOKEN not configured in .env');
            return self::FAILURE;
        }

        $apiUrl = "https://api.telegram.org/bot{$token}";

        if ($this->option('remove')) {
            $response = Http::post("{$apiUrl}/deleteWebhook");
            $this->info('Webhook removed: ' . json_encode($response->json()));
            return self::SUCCESS;
        }

        $webhookUrl = rtrim(config('app.url'), '/') . '/api/telegram/webhook';
        $secret = config('services.telegram.webhook_secret');

        $response = Http::post("{$apiUrl}/setWebhook", [
            'url' => $webhookUrl,
            'secret_token' => $secret,
            'allowed_updates' => ['callback_query', 'message'],
        ]);

        $result = $response->json();
        if ($result['ok'] ?? false) {
            $this->info("Webhook set to: {$webhookUrl}");
        } else {
            $this->error('Failed: ' . json_encode($result));
        }

        return self::SUCCESS;
    }
}
