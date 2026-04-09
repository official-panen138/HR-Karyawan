import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import api from '@/lib/axios'
import type { PaginatedResponse } from '@/types/api'
import type { LeaveRequest } from '@/types/leave'
import { formatDate } from '@/lib/utils'
import { cn } from '@/lib/utils'

type StatusFilter = '' | 'pending' | 'approved' | 'rejected'

const statusColors: Record<string, string> = {
  draft: 'bg-gray-100 text-gray-700',
  pending: 'bg-yellow-100 text-yellow-800',
  partially_approved: 'bg-blue-100 text-blue-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
  cancelled: 'bg-gray-100 text-gray-600',
}

const statusLabels: Record<string, string> = {
  draft: 'Draft',
  pending: 'Menunggu',
  partially_approved: 'Sebagian Disetujui',
  approved: 'Disetujui',
  rejected: 'Ditolak',
  cancelled: 'Dibatalkan',
}

export default function LeaveIndex() {
  const [status, setStatus] = useState<StatusFilter>('')
  const [page, setPage] = useState(1)

  const { data, isLoading } = useQuery({
    queryKey: ['leave-requests', status, page],
    queryFn: async () => {
      const params: Record<string, string | number> = { page }
      if (status) params.status = status
      const res = await api.get<PaginatedResponse<LeaveRequest>>('/leave-requests', { params })
      return res.data
    },
  })

  const requests = data?.data ?? []
  const meta = data?.meta

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Pengajuan Cuti</h1>
        <Link
          to="/leave/create"
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
        >
          Ajukan Cuti
        </Link>
      </div>

      {/* Filter */}
      <div className="bg-white border border-gray-200 rounded-lg p-4">
        <div className="flex items-center gap-2">
          <label className="text-sm font-medium text-gray-700">Status:</label>
          <select
            value={status}
            onChange={(e) => {
              setStatus(e.target.value as StatusFilter)
              setPage(1)
            }}
            className="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua</option>
            <option value="pending">Menunggu</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : requests.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Tidak ada pengajuan cuti.</div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200">
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Karyawan</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Tipe Cuti</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Tanggal</th>
                    <th className="text-center py-3 px-4 text-gray-600 font-medium">Total Hari</th>
                    <th className="text-center py-3 px-4 text-gray-600 font-medium">Status</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Diajukan</th>
                  </tr>
                </thead>
                <tbody>
                  {requests.map((req) => (
                    <tr key={req.id} className="border-b border-gray-100 hover:bg-gray-50">
                      <td className="py-3 px-4">{req.employee_id}</td>
                      <td className="py-3 px-4">{req.leave_type?.name ?? '-'}</td>
                      <td className="py-3 px-4">
                        {formatDate(req.start_date)} - {formatDate(req.end_date)}
                      </td>
                      <td className="py-3 px-4 text-center">{req.total_days}</td>
                      <td className="py-3 px-4 text-center">
                        <span
                          className={cn(
                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                            statusColors[req.status] ?? 'bg-gray-100 text-gray-700'
                          )}
                        >
                          {statusLabels[req.status] ?? req.status}
                        </span>
                      </td>
                      <td className="py-3 px-4">
                        {req.submitted_at ? formatDate(req.submitted_at) : '-'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            {meta && meta.last_page > 1 && (
              <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200">
                <p className="text-sm text-gray-600">
                  Halaman {meta.current_page} dari {meta.last_page} ({meta.total} data)
                </p>
                <div className="flex gap-2">
                  <button
                    onClick={() => setPage((p) => Math.max(1, p - 1))}
                    disabled={page <= 1}
                    className="px-3 py-1 text-sm border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50"
                  >
                    Sebelumnya
                  </button>
                  <button
                    onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
                    disabled={page >= meta.last_page}
                    className="px-3 py-1 text-sm border border-gray-300 rounded-lg disabled:opacity-50 hover:bg-gray-50"
                  >
                    Selanjutnya
                  </button>
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  )
}
