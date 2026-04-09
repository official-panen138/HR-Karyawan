<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreIpWhitelistRequest;
use App\Models\IpWhitelist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpWhitelistController extends BaseController
{
    public function index(): JsonResponse
    {
        $ips = IpWhitelist::with(['division', 'addedByUser'])->orderBy('label')->get();
        return $this->success($ips);
    }

    public function store(StoreIpWhitelistRequest $request): JsonResponse
    {
        $ip = IpWhitelist::create([
            ...$request->validated(),
            'added_by' => $request->user()->id,
        ]);

        return $this->success($ip->load(['division', 'addedByUser']), 'IP berhasil ditambahkan', 201);
    }

    public function update(Request $request, IpWhitelist $ipWhitelist): JsonResponse
    {
        $this->authorize('ip_whitelist.update');

        $data = $request->validate([
            'ip_address' => ['sometimes', 'ip'],
            'label' => ['sometimes', 'string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $ipWhitelist->update($data);
        return $this->success($ipWhitelist->fresh(['division']), 'IP diperbarui');
    }

    public function destroy(IpWhitelist $ipWhitelist): JsonResponse
    {
        $this->authorize('ip_whitelist.delete');
        $ipWhitelist->delete();
        return $this->success(null, 'IP dihapus');
    }
}
