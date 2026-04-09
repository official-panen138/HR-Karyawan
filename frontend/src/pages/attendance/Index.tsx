import { useState, useEffect, useCallback } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { Attendance, BreakLog } from '@/types/attendance'
import { formatDateTime } from '@/lib/utils'

interface TodayAttendance extends Attendance {
  break_logs: BreakLog[]
  active_break: BreakLog | null
}

type BreakCategory = 'smoke' | 'toilet' | 'go_out'

const breakCategoryLabels: Record<BreakCategory, string> = {
  smoke: 'Istirahat Rokok',
  toilet: 'Ke Toilet',
  go_out: 'Keluar',
}

function BreakTimer({ startedAt }: { startedAt: string }) {
  const [elapsed, setElapsed] = useState('')

  const updateElapsed = useCallback(() => {
    const start = new Date(startedAt).getTime()
    const diff = Math.floor((Date.now() - start) / 1000)
    const mins = Math.floor(diff / 60)
    const secs = diff % 60
    setElapsed(`${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`)
  }, [startedAt])

  useEffect(() => {
    updateElapsed()
    const interval = setInterval(updateElapsed, 1000)
    return () => clearInterval(interval)
  }, [updateElapsed])

  return (
    <span className="text-2xl font-mono font-bold text-orange-600">{elapsed}</span>
  )
}

export default function AttendanceIndex() {
  const queryClient = useQueryClient()

  const { data: todayData, isLoading } = useQuery({
    queryKey: ['attendance', 'today'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<TodayAttendance>>('/attendance/today')
      return res.data.data
    },
  })

  const checkIn = useMutation({
    mutationFn: () => api.post('/attendance/check-in'),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['attendance', 'today'] }),
  })

  const checkOut = useMutation({
    mutationFn: () => api.post('/attendance/check-out'),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['attendance', 'today'] }),
  })

  const startBreak = useMutation({
    mutationFn: (category: BreakCategory) =>
      api.post('/attendance/break/start', { category }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['attendance', 'today'] }),
  })

  const endBreak = useMutation({
    mutationFn: () => api.post('/attendance/break/end'),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['attendance', 'today'] }),
  })

  const hasCheckedIn = !!todayData?.check_in_at
  const hasCheckedOut = !!todayData?.check_out_at
  const activeBreak = todayData?.active_break ?? null

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-gray-500">Memuat data kehadiran...</p>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Kehadiran Hari Ini</h1>

      {/* Status Card */}
      <div className="bg-white border border-gray-200 rounded-lg p-6">
        <h2 className="text-lg font-semibold text-gray-800 mb-4">Status Kehadiran</h2>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div className="text-center">
            <p className="text-sm text-gray-500">Check In</p>
            <p className="text-lg font-medium text-gray-900">
              {todayData?.check_in_at ? formatDateTime(todayData.check_in_at) : '-'}
            </p>
          </div>
          <div className="text-center">
            <p className="text-sm text-gray-500">Check Out</p>
            <p className="text-lg font-medium text-gray-900">
              {todayData?.check_out_at ? formatDateTime(todayData.check_out_at) : '-'}
            </p>
          </div>
          <div className="text-center">
            <p className="text-sm text-gray-500">Status</p>
            <p className="text-lg font-medium text-gray-900">
              {todayData?.status ?? 'Belum Absen'}
            </p>
          </div>
        </div>

        <div className="flex gap-4">
          {!hasCheckedIn && (
            <button
              onClick={() => checkIn.mutate()}
              disabled={checkIn.isPending}
              className="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
            >
              {checkIn.isPending ? 'Memproses...' : 'Check In'}
            </button>
          )}
          {hasCheckedIn && !hasCheckedOut && (
            <button
              onClick={() => checkOut.mutate()}
              disabled={checkOut.isPending}
              className="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
            >
              {checkOut.isPending ? 'Memproses...' : 'Check Out'}
            </button>
          )}
          {hasCheckedOut && (
            <p className="text-green-600 font-medium py-2">
              Anda sudah menyelesaikan kehadiran hari ini.
            </p>
          )}
        </div>

        {checkIn.isError && (
          <p className="mt-2 text-sm text-red-600">Gagal check in. Silakan coba lagi.</p>
        )}
        {checkOut.isError && (
          <p className="mt-2 text-sm text-red-600">Gagal check out. Silakan coba lagi.</p>
        )}
      </div>

      {/* Break Panel */}
      {hasCheckedIn && !hasCheckedOut && (
        <div className="bg-white border border-gray-200 rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Istirahat</h2>

          {activeBreak ? (
            <div className="text-center space-y-4">
              <p className="text-sm text-gray-500">
                Sedang istirahat: {breakCategoryLabels[activeBreak.category]}
              </p>
              <BreakTimer startedAt={activeBreak.started_at} />
              <div>
                <button
                  onClick={() => endBreak.mutate()}
                  disabled={endBreak.isPending}
                  className="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50 transition-colors"
                >
                  {endBreak.isPending ? 'Memproses...' : 'Akhiri Istirahat'}
                </button>
              </div>
            </div>
          ) : (
            <div className="flex flex-wrap gap-3">
              {(Object.keys(breakCategoryLabels) as BreakCategory[]).map((cat) => (
                <button
                  key={cat}
                  onClick={() => startBreak.mutate(cat)}
                  disabled={startBreak.isPending}
                  className="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 transition-colors"
                >
                  {breakCategoryLabels[cat]}
                </button>
              ))}
            </div>
          )}

          {startBreak.isError && (
            <p className="mt-2 text-sm text-red-600">Gagal memulai istirahat.</p>
          )}
          {endBreak.isError && (
            <p className="mt-2 text-sm text-red-600">Gagal mengakhiri istirahat.</p>
          )}
        </div>
      )}

      {/* Break Logs */}
      {todayData?.break_logs && todayData.break_logs.length > 0 && (
        <div className="bg-white border border-gray-200 rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Riwayat Istirahat</h2>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-200">
                  <th className="text-left py-2 px-3 text-gray-600">Kategori</th>
                  <th className="text-left py-2 px-3 text-gray-600">Mulai</th>
                  <th className="text-left py-2 px-3 text-gray-600">Selesai</th>
                  <th className="text-left py-2 px-3 text-gray-600">Durasi (menit)</th>
                </tr>
              </thead>
              <tbody>
                {todayData.break_logs.map((log) => (
                  <tr key={log.id} className="border-b border-gray-100">
                    <td className="py-2 px-3">{breakCategoryLabels[log.category]}</td>
                    <td className="py-2 px-3">{formatDateTime(log.started_at)}</td>
                    <td className="py-2 px-3">
                      {log.ended_at ? formatDateTime(log.ended_at) : 'Berlangsung'}
                    </td>
                    <td className="py-2 px-3">
                      {log.duration_minutes ?? '-'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  )
}
