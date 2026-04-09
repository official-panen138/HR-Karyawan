import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { Shift } from '@/types/attendance'

const shiftSchema = z.object({
  name: z.string().min(1, 'Nama wajib diisi'),
  start_time: z.string().min(1, 'Waktu mulai wajib diisi'),
  end_time: z.string().min(1, 'Waktu selesai wajib diisi'),
  crosses_midnight: z.boolean(),
  color: z.string().min(1, 'Warna wajib diisi'),
  is_active: z.boolean(),
})

type ShiftForm = z.infer<typeof shiftSchema>

export default function ShiftsIndex() {
  const queryClient = useQueryClient()
  const [editingId, setEditingId] = useState<string | null>(null)
  const [showCreate, setShowCreate] = useState(false)

  const { data: shifts, isLoading } = useQuery({
    queryKey: ['shifts'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Shift[]>>('/shifts')
      return res.data.data
    },
  })

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<ShiftForm>({
    resolver: zodResolver(shiftSchema),
    defaultValues: {
      crosses_midnight: false,
      is_active: true,
      color: '#3B82F6',
    },
  })

  const createMutation = useMutation({
    mutationFn: (data: ShiftForm) => api.post('/shifts', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['shifts'] })
      setShowCreate(false)
      reset()
    },
  })

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: string; data: ShiftForm }) =>
      api.put(`/shifts/${id}`, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['shifts'] })
      setEditingId(null)
      reset()
    },
  })

  const startEdit = (shift: Shift) => {
    setEditingId(shift.id)
    setShowCreate(false)
    reset({
      name: shift.name,
      start_time: shift.start_time,
      end_time: shift.end_time,
      crosses_midnight: shift.crosses_midnight,
      color: shift.color,
      is_active: shift.is_active,
    })
  }

  const onSubmit = (data: ShiftForm) => {
    if (editingId) {
      updateMutation.mutate({ id: editingId, data })
    } else {
      createMutation.mutate(data)
    }
  }

  const cancelForm = () => {
    setEditingId(null)
    setShowCreate(false)
    reset()
  }

  const isFormOpen = showCreate || editingId !== null

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">Shift Kerja</h1>
        {!isFormOpen && (
          <button
            onClick={() => {
              setShowCreate(true)
              setEditingId(null)
              reset({
                name: '',
                start_time: '',
                end_time: '',
                crosses_midnight: false,
                color: '#3B82F6',
                is_active: true,
              })
            }}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
          >
            Tambah Shift
          </button>
        )}
      </div>

      {/* Create/Edit Form */}
      {isFormOpen && (
        <div className="bg-white border border-gray-200 rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">
            {editingId ? 'Edit Shift' : 'Tambah Shift Baru'}
          </h2>
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input
                  type="text"
                  {...register('name')}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.name && (
                  <p className="mt-1 text-sm text-red-600">{errors.name.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Warna</label>
                <input
                  type="color"
                  {...register('color')}
                  className="w-full h-10 border border-gray-300 rounded-lg cursor-pointer"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                <input
                  type="time"
                  {...register('start_time')}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.start_time && (
                  <p className="mt-1 text-sm text-red-600">{errors.start_time.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                <input
                  type="time"
                  {...register('end_time')}
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.end_time && (
                  <p className="mt-1 text-sm text-red-600">{errors.end_time.message}</p>
                )}
              </div>
            </div>

            <div className="flex items-center gap-6">
              <div className="flex items-center gap-2">
                <input
                  type="checkbox"
                  {...register('crosses_midnight')}
                  id="crosses_midnight"
                  className="rounded border-gray-300"
                />
                <label htmlFor="crosses_midnight" className="text-sm text-gray-700">
                  Melewati tengah malam
                </label>
              </div>
              <div className="flex items-center gap-2">
                <input
                  type="checkbox"
                  {...register('is_active')}
                  id="is_active"
                  className="rounded border-gray-300"
                />
                <label htmlFor="is_active" className="text-sm text-gray-700">
                  Aktif
                </label>
              </div>
            </div>

            <div className="flex gap-3">
              <button
                type="submit"
                disabled={createMutation.isPending || updateMutation.isPending}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm"
              >
                {createMutation.isPending || updateMutation.isPending
                  ? 'Menyimpan...'
                  : 'Simpan'}
              </button>
              <button
                type="button"
                onClick={cancelForm}
                className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
              >
                Batal
              </button>
            </div>

            {(createMutation.isError || updateMutation.isError) && (
              <p className="text-sm text-red-600">Gagal menyimpan. Silakan coba lagi.</p>
            )}
          </form>
        </div>
      )}

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : !shifts || shifts.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Belum ada shift.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="text-left py-3 px-4 text-gray-600 font-medium w-8"></th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Nama</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Waktu Mulai</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Waktu Selesai</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Lewat Tengah Malam</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Status</th>
                  <th className="text-right py-3 px-4 text-gray-600 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody>
                {shifts.map((shift) => (
                  <tr key={shift.id} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4">
                      <div
                        className="w-4 h-4 rounded-full"
                        style={{ backgroundColor: shift.color }}
                      />
                    </td>
                    <td className="py-3 px-4 font-medium">{shift.name}</td>
                    <td className="py-3 px-4">{shift.start_time}</td>
                    <td className="py-3 px-4">{shift.end_time}</td>
                    <td className="py-3 px-4 text-center">
                      {shift.crosses_midnight ? 'Ya' : 'Tidak'}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <span
                        className={`inline-block px-2 py-0.5 rounded-full text-xs font-medium ${
                          shift.is_active
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-600'
                        }`}
                      >
                        {shift.is_active ? 'Aktif' : 'Nonaktif'}
                      </span>
                    </td>
                    <td className="py-3 px-4 text-right">
                      <button
                        onClick={() => startEdit(shift)}
                        className="text-blue-600 hover:text-blue-800 text-sm"
                      >
                        Edit
                      </button>
                    </td>
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
