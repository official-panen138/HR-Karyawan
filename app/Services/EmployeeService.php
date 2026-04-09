<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryHistory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Employee::with(['position', 'division'])
            ->when($filters['division_id'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('full_name', 'ilike', "%{$v}%"))
            ->orderBy($filters['sort'] ?? 'full_name', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, ?object $photo = null): Employee
    {
        return DB::transaction(function () use ($data, $photo) {
            if ($photo) {
                $data['photo_path'] = $photo->store('employees/photos', 'r2');
            }

            $employee = Employee::create($data);

            // Create user account
            $user = User::create([
                'name' => $data['full_name'],
                'email' => Str::slug($data['full_name']) . '@company.com',
                'password' => Hash::make('password'),
                'employee_id' => $employee->id,
            ]);
            $user->assignRole('staff');

            return $employee->load(['position', 'division']);
        });
    }

    public function update(Employee $employee, array $data, ?object $photo = null): Employee
    {
        return DB::transaction(function () use ($employee, $data, $photo) {
            // Track salary change
            if (isset($data['current_salary']) && $data['current_salary'] != $employee->current_salary) {
                SalaryHistory::create([
                    'employee_id' => $employee->id,
                    'old_salary' => $employee->current_salary,
                    'new_salary' => $data['current_salary'],
                    'effective_date' => now(),
                    'reason' => $data['salary_reason'] ?? null,
                    'changed_by' => auth()->id(),
                ]);
            }

            if ($photo) {
                if ($employee->photo_path) {
                    Storage::disk('r2')->delete($employee->photo_path);
                }
                $data['photo_path'] = $photo->store('employees/photos', 'r2');
            }

            unset($data['salary_reason']);
            $employee->update($data);

            return $employee->fresh(['position', 'division']);
        });
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            if ($employee->photo_path) {
                Storage::disk('r2')->delete($employee->photo_path);
            }
            $employee->user?->delete();
            $employee->delete();
        });
    }
}
