import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { LeaveRequest } from '@/types/leave'
import { formatDate } from '@/lib/utils'

interface LeaveApproval {
  id: string
  leave_request_id: string
  leave_request: LeaveRequest & {
    employee_name: string
  }
}

export default function LeaveApprovals() {
  const queryClient = useQueryClient()
  const [rejectingId, setRejectingId] = useState<string | null>(null)
  const [rejectNotes, setRejectNotes] = useState('')

  const { data: approvals, isLoading } = useQuery({
    queryKey: ['leave-approvals', 'pending'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<LeaveApproval[]>>('/leave-approvals/pending')
      return res.data.data
    },
  })

  const approveMutation = useMutation({
    mutationFn: (id: string) => api.post(`/leave-approvals/${id}/approve`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['leave-approvals', 'pending'] }),
  })

  const rejectMutation = useMutation({
    mutationFn: ({ id, notes }: { id: string; notes: string }) =>
      api.post(`/leave-approvals/${id}/reject`, { notes }),
    onSuccess: () => {
      setRejectingId(null)
      setRejectNotes('')
      queryClient.invalidateQueries({ queryKey: ['leave-approvals', 'pending'] })
    },
  })

  const handleReject = (id: string) => {
    rejectMutation.mutate({ id, notes: rejectNotes })
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-gray-500">Memuat data persetujuan...</p>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Persetujuan Cuti</h1>

      {!approvals || approvals.length === 0 ? (
        <div className="bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
          Tidak ada pengajuan cuti yang menunggu persetujuan.
        </div>
      ) : (
        <div className="space-y-4">
          {approvals.map((approval) => {
            const req = approval.leave_request
            const isRejecting = rejectingId === approval.id

            return (
              <div
                key={approval.id}
                className="bg-white border border-gray-200 rounded-lg p-6"
              >
                <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                  <div className="space-y-2">
                    <h3 className="font-semibold text-gray-900">{req.employee_name}</h3>
                    <p className="text-sm text-gray-600">
                      <span className="font-medium">Tipe:</span>{' '}
                      {req.leave_type?.name ?? '-'}
                    </p>
                    <p className="text-sm text-gray-600">
                      <span className="font-medium">Tanggal:</span>{' '}
                      {formatDate(req.start_date)} - {formatDate(req.end_date)}{' '}
                      ({req.total_days} hari)
                    </p>
                    {req.reason && (
                      <p className="text-sm text-gray-600">
                        <span className="font-medium">Alasan:</span> {req.reason}
                      </p>
                    )}
                    {req.is_emergency && (
                      <span className="inline-block px-2 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                        Darurat
                      </span>
                    )}
                  </div>

                  <div className="flex flex-col gap-2 min-w-[140px]">
                    <button
                      onClick={() => approveMutation.mutate(approval.id)}
                      disabled={approveMutation.isPending}
                      className="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                    >
                      {approveMutation.isPending ? 'Memproses...' : 'Setujui'}
                    </button>
                    <button
                      onClick={() =>
                        setRejectingId(isRejecting ? null : approval.id)
                      }
                      className="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                    >
                      Tolak
                    </button>
                  </div>
                </div>

                {isRejecting && (
                  <div className="mt-4 pt-4 border-t border-gray-200">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Catatan Penolakan
                    </label>
                    <textarea
                      value={rejectNotes}
                      onChange={(e) => setRejectNotes(e.target.value)}
                      rows={2}
                      className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
                      placeholder="Alasan penolakan"
                    />
                    <div className="flex gap-2">
                      <button
                        onClick={() => handleReject(approval.id)}
                        disabled={rejectMutation.isPending}
                        className="px-4 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                      >
                        {rejectMutation.isPending ? 'Memproses...' : 'Konfirmasi Tolak'}
                      </button>
                      <button
                        onClick={() => {
                          setRejectingId(null)
                          setRejectNotes('')
                        }}
                        className="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors"
                      >
                        Batal
                      </button>
                    </div>
                  </div>
                )}

                {approveMutation.isError && (
                  <p className="mt-2 text-sm text-red-600">Gagal menyetujui. Silakan coba lagi.</p>
                )}
                {rejectMutation.isError && (
                  <p className="mt-2 text-sm text-red-600">Gagal menolak. Silakan coba lagi.</p>
                )}
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}
