<?php

namespace App\Console\Commands;

use App\Models\EmployeeDocument;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class SendDocumentExpiryReminders extends Command
{
    protected $signature = 'hr:remind-document-expiry';
    protected $description = 'Send reminders for expiring employee documents (90, 30, 7 days)';

    public function handle(TelegramService $telegram): int
    {
        $thresholds = [
            ['days' => 90, 'field' => 'reminded_at_90', 'label' => '3 bulan'],
            ['days' => 30, 'field' => 'reminded_at_30', 'label' => '1 bulan'],
            ['days' => 7, 'field' => 'reminded_at_7', 'label' => '1 minggu'],
        ];

        $chatId = config('services.telegram.hr_group_chat_id');
        $sent = 0;

        foreach ($thresholds as $threshold) {
            $documents = EmployeeDocument::with('employee')
                ->whereNotNull('expired_at')
                ->whereDate('expired_at', '<=', now()->addDays($threshold['days']))
                ->whereDate('expired_at', '>', now())
                ->whereNull($threshold['field'])
                ->get();

            foreach ($documents as $doc) {
                $daysLeft = (int) now()->diffInDays($doc->expired_at, false);

                if ($chatId) {
                    $telegram->sendMessage($chatId,
                        "⚠️ <b>Dokumen akan kadaluarsa dalam {$threshold['label']}</b>\n\n"
                        . "Nama: {$doc->employee->full_name}\n"
                        . "Dokumen: {$doc->doc_type->value}\n"
                        . "No: {$doc->doc_number}\n"
                        . "Kadaluarsa: {$doc->expired_at->format('d M Y')} ({$daysLeft} hari lagi)"
                    );
                }

                $doc->update([$threshold['field'] => now()]);
                $sent++;
            }
        }

        $this->info("Sent {$sent} document expiry reminders.");
        return self::SUCCESS;
    }
}
