<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
use App\Models\LeaveApproval;
use App\Services\LeaveApprovalService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends BaseController
{
    public function __construct(
        private TelegramService $telegram,
        private LeaveApprovalService $leaveService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($secret !== config('services.telegram.webhook_secret')) {
            return $this->error('Unauthorized', 401);
        }

        $update = $request->all();

        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return $this->success(null, 'OK');
    }

    private function handleCallbackQuery(array $callback): void
    {
        $data = $callback['data'] ?? '';
        $parts = explode(':', $data);

        if (count($parts) !== 3) {
            $this->telegram->answerCallbackQuery($callback['id'], 'Data tidak valid');
            return;
        }

        [$action, $approvalId, $secret] = $parts;

        if ($secret !== config('services.telegram.callback_secret')) {
            $this->telegram->answerCallbackQuery($callback['id'], 'Secret tidak valid');
            return;
        }

        $approval = LeaveApproval::with(['leaveRequest.employee', 'approver'])->find($approvalId);

        if (!$approval || $approval->status !== ApprovalStatus::PENDING) {
            $this->telegram->answerCallbackQuery($callback['id'], 'Approval sudah diproses');
            return;
        }

        try {
            if ($action === 'approve') {
                $this->leaveService->approve($approvalId, $approval->approver_id, 'Disetujui via Telegram');
                $statusText = 'DISETUJUI';
            } elseif ($action === 'reject') {
                $this->leaveService->reject($approvalId, $approval->approver_id, 'Ditolak via Telegram');
                $statusText = 'DITOLAK';
            } else {
                $this->telegram->answerCallbackQuery($callback['id'], 'Action tidak valid');
                return;
            }

            // Update original message
            $chatId = $callback['message']['chat']['id'] ?? null;
            $messageId = $callback['message']['message_id'] ?? null;

            if ($chatId && $messageId) {
                $employeeName = $approval->leaveRequest->employee->full_name;
                $this->telegram->editMessage(
                    (string) $chatId,
                    $messageId,
                    "<b>Cuti {$statusText}</b>\n\nNama: {$employeeName}\nOleh: {$approval->approver->name}"
                );
            }

            $this->telegram->answerCallbackQuery($callback['id'], "Cuti berhasil {$statusText}");
        } catch (\Throwable $e) {
            $this->telegram->answerCallbackQuery($callback['id'], 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
