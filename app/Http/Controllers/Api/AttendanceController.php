<?php

namespace App\Http\Controllers\Api;

use App\Enums\BreakCategory;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\BreakLogResource;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends BaseController
{
    public function __construct(private AttendanceService $service) {}

    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['employee.position', 'employee.division', 'shift', 'breakLogs'])
            ->when($request->date, fn ($q, $v) => $q->where('date', $v))
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->division_id, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('division_id', $request->division_id)))
            ->orderByDesc('date');

        $attendances = $query->paginate($request->per_page ?? 15);

        return $this->paginated(
            $attendances->setCollection(
                $attendances->getCollection()->map(fn ($a) => new AttendanceResource($a))
            )
        );
    }

    public function today(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        $attendance = Attendance::with(['shift', 'breakLogs'])
            ->where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();

        return $this->success($attendance ? new AttendanceResource($attendance) : null);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        try {
            $attendance = $this->service->checkIn($employee, $request->ip());
            return $this->success(new AttendanceResource($attendance), 'Check-in berhasil');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function checkOut(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        try {
            $attendance = $this->service->checkOut($employee, $request->ip());
            return $this->success(new AttendanceResource($attendance), 'Check-out berhasil');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function startBreak(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['required', 'in:smoke,toilet,go_out'],
        ]);

        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->error('Akun tidak terhubung dengan data karyawan', 404);
        }

        try {
            $breakLog = $this->service->startBreak(
                $employee,
                BreakCategory::from($request->category),
                $request->ip()
            );
            return $this->success(new BreakLogResource($breakLog), 'Break dimulai');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function endBreak(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->firstOrFail();

        $activeBreak = $attendance->breakLogs()->whereNull('ended_at')->first();

        if (!$activeBreak) {
            return $this->error('Tidak ada break aktif', 422);
        }

        try {
            $breakLog = $this->service->endBreak($activeBreak);
            return $this->success(new BreakLogResource($breakLog), 'Break selesai');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $attendances = Attendance::with(['employee.position', 'employee.division', 'shift'])
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->when($request->division_id, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('division_id', $request->division_id)))
            ->orderBy('date')
            ->get();

        return $this->success(AttendanceResource::collection($attendances));
    }
}
