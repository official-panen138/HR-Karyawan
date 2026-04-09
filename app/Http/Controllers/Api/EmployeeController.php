<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{
    public function __construct(private EmployeeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $employees = $this->service->list($request->all());

        return $this->paginated(
            $employees->setCollection(
                $employees->getCollection()->map(fn ($e) => new EmployeeResource($e))
            )
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create(
            $request->validated(),
            $request->file('photo')
        );

        return $this->success(new EmployeeResource($employee), 'Karyawan berhasil ditambahkan', 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['position', 'division', 'user', 'salaryHistories', 'documents']);

        return $this->success(new EmployeeResource($employee));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee = $this->service->update(
            $employee,
            $request->validated(),
            $request->file('photo')
        );

        return $this->success(new EmployeeResource($employee), 'Data karyawan diperbarui');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('employees.delete');
        $this->service->delete($employee);

        return $this->success(null, 'Karyawan berhasil dihapus');
    }
}
