<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AssignShiftRequest;
use App\Http\Requests\StoreShiftRequest;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends BaseController
{
    public function index(): JsonResponse
    {
        $shifts = Shift::with('division')->orderBy('name')->get();
        return $this->success($shifts);
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = Shift::create($request->validated());
        return $this->success($shift, 'Shift berhasil ditambahkan', 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        $shift->load(['division', 'breakQuotas']);
        return $this->success($shift);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $this->authorize('shifts.update');

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:50'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'crosses_midnight' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'string', 'max:7'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $shift->update($data);
        return $this->success($shift->fresh(), 'Shift diperbarui');
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $this->authorize('shifts.delete');
        $shift->delete();
        return $this->success(null, 'Shift dihapus');
    }

    public function assign(AssignShiftRequest $request): JsonResponse
    {
        $assignment = EmployeeShift::create([
            ...$request->validated(),
            'assigned_by' => $request->user()->id,
        ]);

        return $this->success($assignment->load(['employee', 'shift']), 'Shift berhasil di-assign', 201);
    }

    public function assignments(Request $request): JsonResponse
    {
        $assignments = EmployeeShift::with(['employee.division', 'shift', 'assignedByUser'])
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->orderByDesc('start_date')
            ->paginate($request->per_page ?? 15);

        return $this->paginated($assignments);
    }

    public function removeAssignment(EmployeeShift $employeeShift): JsonResponse
    {
        $this->authorize('shifts.assign');
        $employeeShift->delete();
        return $this->success(null, 'Assignment dihapus');
    }
}
