<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\EmployeeDocument;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends BaseController
{
    public function attendance(Request $request): StreamedResponse
    {
        $this->authorize('reports.export');

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $rows = Attendance::with(['employee.division', 'employee.position', 'shift'])
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->when($request->division_id, fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('division_id', $request->division_id)))
            ->orderBy('date')
            ->get();

        return $this->streamCsv("attendance_{$request->start_date}_{$request->end_date}.csv", [
            'Tanggal', 'Nama', 'Divisi', 'Posisi', 'Shift', 'Check In', 'Check Out',
            'Terlambat (menit)', 'Efektif (menit)', 'Status',
        ], $rows->map(fn ($r) => [
            $r->date->format('Y-m-d'),
            $r->employee->full_name,
            $r->employee->division->name ?? '-',
            $r->employee->position->name ?? '-',
            $r->shift->name ?? '-',
            $r->check_in_at?->format('H:i:s') ?? '-',
            $r->check_out_at?->format('H:i:s') ?? '-',
            $r->late_minutes,
            $r->effective_minutes,
            $r->status->value,
        ]));
    }

    public function leave(Request $request): StreamedResponse
    {
        $this->authorize('reports.export');

        $rows = LeaveRequest::with(['employee.division', 'leaveType'])
            ->when($request->year, fn ($q, $y) => $q->whereYear('start_date', $y))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->get();

        $year = $request->year ?? now()->year;

        return $this->streamCsv("leave_report_{$year}.csv", [
            'Nama', 'Divisi', 'Jenis Cuti', 'Mulai', 'Selesai',
            'Total Hari', 'Status', 'Darurat', 'Diajukan',
        ], $rows->map(fn ($r) => [
            $r->employee->full_name,
            $r->employee->division->name ?? '-',
            $r->leaveType->name,
            $r->start_date->format('Y-m-d'),
            $r->end_date->format('Y-m-d'),
            $r->total_days,
            $r->status->value,
            $r->is_emergency ? 'Ya' : 'Tidak',
            $r->submitted_at?->format('Y-m-d H:i') ?? '-',
        ]));
    }

    public function documents(): StreamedResponse
    {
        $this->authorize('reports.export');

        $rows = EmployeeDocument::with(['employee.division'])
            ->orderBy('expired_at')
            ->get();

        return $this->streamCsv('documents_report.csv', [
            'Nama', 'Divisi', 'Jenis Dokumen', 'Nomor', 'Terbit',
            'Kadaluarsa', 'Status', 'Pemegang', 'Status Perpanjangan',
        ], $rows->map(fn ($r) => [
            $r->employee->full_name,
            $r->employee->division->name ?? '-',
            $r->doc_type->value,
            $r->doc_number ?? '-',
            $r->issued_at?->format('Y-m-d') ?? '-',
            $r->expired_at?->format('Y-m-d') ?? '-',
            $r->status,
            $r->holder->value,
            $r->renewal_status->value,
        ]));
    }

    private function streamCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, is_array($row) ? $row : $row->toArray());
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
