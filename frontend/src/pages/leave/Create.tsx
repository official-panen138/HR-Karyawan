import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useQuery, useMutation } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { LeaveType } from '@/types/leave'

const leaveSchema = z.object({
  leave_type_id: z.string().min(1, 'Tipe cuti wajib dipilih'),
  start_date: z.string().min(1, 'Tanggal mulai wajib diisi'),
  end_date: z.string().min(1, 'Tanggal akhir wajib diisi'),
  reason: z.string().min(1, 'Alasan wajib diisi'),
  is_emergency: z.boolean(),
  contact_during_leave: z.string().optional(),
  handover_notes: z.string().optional(),
})

type LeaveForm = z.infer<typeof leaveSchema>

export default function LeaveCreate() {
  const navigate = useNavigate()

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors },
  } = useForm<LeaveForm>({
    resolver: zodResolver(leaveSchema),
    defaultValues: {
      is_emergency: false,
    },
  })

  const startDate = watch('start_date')
  const endDate = watch('end_date')

  const { data: leaveTypes } = useQuery({
    queryKey: ['leave-types'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<LeaveType[]>>('/leave/types')
      return res.data.data
    },
  })

  const { data: calculatedDays, isFetching: isCalculating } = useQuery({
    queryKey: ['leave-calculate-days', startDate, endDate],
    queryFn: async () => {
      const res = await api.get<ApiResponse<{ working_days: number }>>('/leave/calculate-days', {
        params: { start_date: startDate, end_date: endDate },
      })
      return res.data.data.working_days
    },
    enabled: !!startDate && !!endDate && startDate <= endDate,
  })

  const submitMutation = useMutation({
    mutationFn: (data: LeaveForm) => api.post('/leave-requests', data),
    onSuccess: () => navigate('/leave'),
  })

  const onSubmit = (data: LeaveForm) => {
    submitMutation.mutate(data)
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Ajukan Cuti</h1>

      <div className="bg-white border border-gray-200 rounded-lg p-6">
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
          {/* Leave Type */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Tipe Cuti</label>
            <select
              {...register('leave_type_id')}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Pilih tipe cuti</option>
              {leaveTypes?.map((type) => (
                <option key={type.id} value={type.id}>
                  {type.name}
                </option>
              ))}
            </select>
            {errors.leave_type_id && (
              <p className="mt-1 text-sm text-red-600">{errors.leave_type_id.message}</p>
            )}
          </div>

          {/* Dates */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
              <input
                type="date"
                {...register('start_date')}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              {errors.start_date && (
                <p className="mt-1 text-sm text-red-600">{errors.start_date.message}</p>
              )}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
              <input
                type="date"
                {...register('end_date')}
                className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              {errors.end_date && (
                <p className="mt-1 text-sm text-red-600">{errors.end_date.message}</p>
              )}
            </div>
          </div>

          {/* Calculated Days */}
          {startDate && endDate && startDate <= endDate && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
              <p className="text-sm text-blue-800">
                Hari kerja:{' '}
                <span className="font-semibold">
                  {isCalculating ? 'Menghitung...' : `${calculatedDays ?? '-'} hari`}
                </span>
              </p>
            </div>
          )}

          {/* Reason */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
            <textarea
              {...register('reason')}
              rows={3}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Jelaskan alasan pengajuan cuti"
            />
            {errors.reason && (
              <p className="mt-1 text-sm text-red-600">{errors.reason.message}</p>
            )}
          </div>

          {/* Emergency */}
          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              {...register('is_emergency')}
              id="is_emergency"
              className="rounded border-gray-300"
            />
            <label htmlFor="is_emergency" className="text-sm text-gray-700">
              Cuti darurat
            </label>
          </div>

          {/* Contact */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Kontak Selama Cuti
            </label>
            <input
              type="text"
              {...register('contact_during_leave')}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Nomor telepon atau email"
            />
          </div>

          {/* Handover */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Catatan Serah Terima
            </label>
            <textarea
              {...register('handover_notes')}
              rows={3}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Tugas yang perlu didelegasikan"
            />
          </div>

          {/* Submit */}
          <div className="flex gap-3">
            <button
              type="submit"
              disabled={submitMutation.isPending}
              className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm"
            >
              {submitMutation.isPending ? 'Mengirim...' : 'Ajukan Cuti'}
            </button>
            <button
              type="button"
              onClick={() => navigate('/leave')}
              className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
            >
              Batal
            </button>
          </div>

          {submitMutation.isError && (
            <p className="text-sm text-red-600">Gagal mengajukan cuti. Silakan coba lagi.</p>
          )}
        </form>
      </div>
    </div>
  )
}
