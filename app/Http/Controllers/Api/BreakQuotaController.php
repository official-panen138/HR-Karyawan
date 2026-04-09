<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBreakQuotaRequest;
use App\Models\BreakQuota;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BreakQuotaController extends BaseController
{
    public function index(): JsonResponse
    {
        $quotas = BreakQuota::with(['division', 'shift'])->orderBy('category')->get();
        return $this->success($quotas);
    }

    public function store(StoreBreakQuotaRequest $request): JsonResponse
    {
        $quota = BreakQuota::create($request->validated());
        return $this->success($quota, 'Break quota berhasil ditambahkan', 201);
    }

    public function update(Request $request, BreakQuota $breakQuota): JsonResponse
    {
        $this->authorize('shifts.update');

        $data = $request->validate([
            'quota_minutes' => ['sometimes', 'integer', 'min:1'],
            'global_minutes_limit' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $breakQuota->update($data);
        return $this->success($breakQuota->fresh(), 'Break quota diperbarui');
    }

    public function destroy(BreakQuota $breakQuota): JsonResponse
    {
        $this->authorize('shifts.delete');
        $breakQuota->delete();
        return $this->success(null, 'Break quota dihapus');
    }
}
