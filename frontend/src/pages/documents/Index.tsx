import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { PaginatedResponse } from '@/types/api'
import { formatDate, cn } from '@/lib/utils'

interface Document {
  id: string
  employee_id: string
  employee_name: string
  doc_type: string
  doc_number: string
  expired_at: string | null
  status: 'expired' | 'critical' | 'warning' | 'notice' | 'valid'
  holder: string | null
}

type StatusFilter = '' | 'expired' | 'expiring_soon'

const statusBadge: Record<string, string> = {
  expired: 'bg-red-100 text-red-800',
  critical: 'bg-red-100 text-red-800',
  warning: 'bg-yellow-100 text-yellow-800',
  notice: 'bg-blue-100 text-blue-800',
  valid: 'bg-green-100 text-green-800',
}

const statusLabels: Record<string, string> = {
  expired: 'Kadaluarsa',
  critical: 'Kritis',
  warning: 'Peringatan',
  notice: 'Perhatian',
  valid: 'Aktif',
}

export default function DocumentsIndex() {
  const [status, setStatus] = useState<StatusFilter>('')
  const [page, setPage] = useState(1)

  const { data, isLoading } = useQuery({
    queryKey: ['documents', status, page],
    queryFn: async () => {
      const params: Record<string, string | number> = { page }
      if (status) params.status = status
      const res = await api.get<PaginatedResponse<Document>>('/documents', { params })
      return res.data
    },
  })

  const documents = data?.data ?? []
  const meta = data?.meta

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Dokumen Karyawan</h1>

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
            <option value="expired">Kadaluarsa</option>
            <option value="expiring_soon">Segera Kadaluarsa</option>
          </select>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : documents.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Tidak ada dokumen.</div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200">
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Karyawan</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Tipe Dokumen</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">No. Dokumen</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Kadaluarsa</th>
                    <th className="text-center py-3 px-4 text-gray-600 font-medium">Status</th>
                    <th className="text-left py-3 px-4 text-gray-600 font-medium">Pemegang</th>
                  </tr>
                </thead>
                <tbody>
                  {documents.map((doc) => (
                    <tr key={doc.id} className="border-b border-gray-100 hover:bg-gray-50">
                      <td className="py-3 px-4">{doc.employee_name}</td>
                      <td className="py-3 px-4">{doc.doc_type}</td>
                      <td className="py-3 px-4 font-mono text-xs">{doc.doc_number}</td>
                      <td className="py-3 px-4">
                        {doc.expired_at ? formatDate(doc.expired_at) : '-'}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <span
                          className={cn(
                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                            statusBadge[doc.status] ?? 'bg-gray-100 text-gray-700'
                          )}
                        >
                          {statusLabels[doc.status] ?? doc.status}
                        </span>
                      </td>
                      <td className="py-3 px-4">{doc.holder ?? '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

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
