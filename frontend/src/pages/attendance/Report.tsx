import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { Division } from '@/types/employee'

interface AttendanceReportRow {
  employee_id: string
  employee_name: string
  division: string
  total_days: number
  present: number
  late: number
  absent: number
  avg_effective_minutes: number
}

export default function AttendanceReport() {
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [divisionId, setDivisionId] = useState('')

  const { data: divisions } = useQuery({
    queryKey: ['divisions'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Division[]>>('/divisions')
      return res.data.data
    },
  })

  const { data: report, isLoading, isFetching } = useQuery({
    queryKey: ['reports', 'attendance', startDate, endDate, divisionId],
    queryFn: async () => {
      const params: Record<string, string> = {}
      if (startDate) params.start_date = startDate
      if (endDate) params.end_date = endDate
      if (divisionId) params.division_id = divisionId
      const res = await api.get<ApiResponse<AttendanceReportRow[]>>('/reports/attendance', { params })
      return res.data.data
    },
    enabled: !!startDate && !!endDate,
  })

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Laporan Kehadiran</h1>

      {/* Filters */}
      <div className="bg-white border border-gray-200 rounded-lg p-6">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
            <select
              value={divisionId}
              onChange={(e) => setDivisionId(e.target.value)}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Semua Divisi</option>
              {divisions?.map((div) => (
                <option key={div.id} value={div.id}>
                  {div.name}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading || isFetching ? (
          <div className="p-8 text-center text-gray-500">Memuat laporan...</div>
        ) : !startDate || !endDate ? (
          <div className="p-8 text-center text-gray-500">
            Pilih tanggal mulai dan akhir untuk melihat laporan.
          </div>
        ) : !report || report.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Tidak ada data.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Nama Karyawan</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Divisi</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Total Hari</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Hadir</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Terlambat</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Absen</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Rata-rata Efektif (menit)</th>
                </tr>
              </thead>
              <tbody>
                {report.map((row) => (
                  <tr key={row.employee_id} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">{row.employee_name}</td>
                    <td className="py-3 px-4">{row.division}</td>
                    <td className="py-3 px-4 text-center">{row.total_days}</td>
                    <td className="py-3 px-4 text-center">{row.present}</td>
                    <td className="py-3 px-4 text-center">{row.late}</td>
                    <td className="py-3 px-4 text-center">{row.absent}</td>
                    <td className="py-3 px-4 text-center">{row.avg_effective_minutes}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  )
}
