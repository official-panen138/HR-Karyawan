<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): ?array
    {
        if (empty($this->token)) {
            Log::warning('Telegram bot token not configured');
            return null;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", $payload);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Telegram send failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function sendLeaveApprovalRequest(
        string $chatId,
        string $employeeName,
        string $leaveType,
        string $startDate,
        string $endDate,
        int $totalDays,
        string $approvalId,
    ): ?array {
        $text = "<b>Pengajuan Cuti Baru</b>\n\n"
            . "Nama: <b>{$employeeName}</b>\n"
            . "Jenis: {$leaveType}\n"
            . "Tanggal: {$startDate} s/d {$endDate}\n"
            . "Total: {$totalDays} hari\n\n"
            . "Silakan approve atau reject:";

        $callbackSecret = config('services.telegram.callback_secret', '');

        $replyMarkup = [
            'inline_keyboard' => [[
                [
                    'text' => 'Approve',
                    'callback_data' => "approve:{$approvalId}:{$callbackSecret}",
                ],
                [
                    'text' => 'Reject',
                    'callback_data' => "reject:{$approvalId}:{$callbackSecret}",
                ],
            ]],
        ];

        return $this->sendMessage($chatId, $text, $replyMarkup);
    }

    public function editMessage(string $chatId, int $messageId, string $text): ?array
    {
        if (empty($this->token)) return null;

        try {
            $response = Http::post("{$this->apiUrl}/editMessageText", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Telegram edit failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text): void
    {
        if (empty($this->token)) return;

        Http::post("{$this->apiUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }
}
