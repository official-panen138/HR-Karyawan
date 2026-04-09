<?php

namespace App\Http\Controllers\Api;

use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureFlagController extends BaseController
{
    public function index(): JsonResponse
    {
        $flags = FeatureFlag::orderBy('feature_key')->get();
        return $this->success($flags);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('feature_flags.create');

        $data = $request->validate([
            'feature_key' => ['required', 'string', 'max:80', 'unique:feature_flags'],
            'label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'enabled_for_roles' => ['nullable', 'array'],
            'enabled_for_users' => ['nullable', 'array'],
            'is_globally_enabled' => ['sometimes', 'boolean'],
        ]);

        $flag = FeatureFlag::create($data);
        return $this->success($flag, 'Feature flag berhasil ditambahkan', 201);
    }

    public function update(Request $request, FeatureFlag $featureFlag): JsonResponse
    {
        $this->authorize('feature_flags.update');

        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'enabled_for_roles' => ['nullable', 'array'],
            'enabled_for_users' => ['nullable', 'array'],
            'is_globally_enabled' => ['sometimes', 'boolean'],
        ]);

        $featureFlag->update($data);
        return $this->success($featureFlag->fresh(), 'Feature flag diperbarui');
    }

    public function destroy(FeatureFlag $featureFlag): JsonResponse
    {
        $this->authorize('feature_flags.delete');
        $featureFlag->delete();
        return $this->success(null, 'Feature flag dihapus');
    }

    public function toggle(FeatureFlag $featureFlag): JsonResponse
    {
        $this->authorize('feature_flags.update');
        $featureFlag->update(['is_globally_enabled' => !$featureFlag->is_globally_enabled]);
        return $this->success($featureFlag->fresh(), 'Feature flag di-toggle');
    }
}
