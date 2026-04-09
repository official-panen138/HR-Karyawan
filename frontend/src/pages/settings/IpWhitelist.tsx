import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'
import type { Division } from '@/types/employee'

interface IpWhitelistEntry {
  id: string
  ip_address: string
  label: string
  division_id: string | null
  division?: Division | null
  is_active: boolean
}

const ipSchema = z.object({
  ip_address: z
    .string()
    .min(1, 'IP address wajib diisi')
    .regex(
      /^(\d{1,3}\.){3}\d{1,3}(\/\d{1,2})?$/,
      'Format IP tidak valid'
    ),
  label: z.string().min(1, 'Label wajib diisi'),
  division_id: z.string().optional(),
})

type IpForm = z.infer<typeof ipSchema>

export default function IpWhitelist() {
  const queryClient = useQueryClient()
  const [showForm, setShowForm] = useState(false)

  const { data: entries, isLoading } = useQuery({
    queryKey: ['ip-whitelist'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<IpWhitelistEntry[]>>('/ip-whitelist')
      return res.data.data
    },
  })

  const { data: divisions } = useQuery({
    queryKey: ['divisions'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<Division[]>>('/divisions')
      return res.data.data
    },
  })

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<IpForm>({
    resolver: zodResolver(ipSchema),
  })

  const createMutation = useMutation({
    mutationFn: (data: IpForm) => api.post('/ip-whitelist', data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['ip-whitelist'] })
      setShowForm(false)
      reset()
    },
  })

  const toggleMutation = useMutation({
    mutationFn: (id: string) => api.patch(`/ip-whitelist/${id}/toggle`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['ip-whitelist'] }),
  })

  const onSubmit = (data: IpForm) => {
    createMutation.mutate(data)
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">IP Whitelist</h1>
        {!showForm && (
          <button
            onClick={() => setShowForm(true)}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
          >
            Tambah IP
          </button>
        )}
      </div>

      {/* Add Form */}
      {showForm && (
        <div className="bg-white border border-gray-200 rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-800 mb-4">Tambah IP Baru</h2>
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input
                  type="text"
                  {...register('ip_address')}
                  placeholder="192.168.1.0/24"
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.ip_address && (
                  <p className="mt-1 text-sm text-red-600">{errors.ip_address.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Label</label>
                <input
                  type="text"
                  {...register('label')}
                  placeholder="Kantor Pusat"
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                {errors.label && (
                  <p className="mt-1 text-sm text-red-600">{errors.label.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                <select
                  {...register('division_id')}
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

            <div className="flex gap-3">
              <button
                type="submit"
                disabled={createMutation.isPending}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm"
              >
                {createMutation.isPending ? 'Menyimpan...' : 'Simpan'}
              </button>
              <button
                type="button"
                onClick={() => {
                  setShowForm(false)
                  reset()
                }}
                className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
              >
                Batal
              </button>
            </div>

            {createMutation.isError && (
              <p className="text-sm text-red-600">Gagal menyimpan. Silakan coba lagi.</p>
            )}
          </form>
        </div>
      )}

      {/* Table */}
      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : !entries || entries.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Belum ada IP whitelist.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">IP Address</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Label</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Divisi</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                {entries.map((entry) => (
                  <tr key={entry.id} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 font-mono text-xs">{entry.ip_address}</td>
                    <td className="py-3 px-4">{entry.label}</td>
                    <td className="py-3 px-4">{entry.division?.name ?? 'Semua'}</td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => toggleMutation.mutate(entry.id)}
                        disabled={toggleMutation.isPending}
                        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                          entry.is_active ? 'bg-blue-600' : 'bg-gray-300'
                        }`}
                      >
                        <span
                          className={`inline-block h-4 w-4 rounded-full bg-white transition-transform ${
                            entry.is_active ? 'translate-x-6' : 'translate-x-1'
                          }`}
                        />
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
