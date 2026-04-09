import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import api from '@/lib/axios'
import type { ApiResponse, PaginatedResponse } from '@/types/api'
import { formatDateTime } from '@/lib/utils'

interface UserItem {
  id: string
  name: string
  email: string
  roles: string[]
  employee: { id: string; full_name: string } | null
  last_login_at: string | null
  created_at: string
}

interface RoleItem {
  id: number
  name: string
}

const userSchema = z.object({
  name: z.string().min(1, 'Nama wajib diisi'),
  email: z.string().email('Email tidak valid'),
  password: z.string().min(8, 'Minimal 8 karakter'),
  role: z.string().min(1, 'Role wajib dipilih'),
})

type UserForm = z.infer<typeof userSchema>

export default function Users() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')
  const [roleFilter, setRoleFilter] = useState('')
  const [page, setPage] = useState(1)
  const [showForm, setShowForm] = useState(false)
  const [editingUser, setEditingUser] = useState<UserItem | null>(null)

  const { data, isLoading } = useQuery({
    queryKey: ['users', { search, role: roleFilter, page }],
    queryFn: async () => {
      const res = await api.get<PaginatedResponse<UserItem>>('/users', {
        params: { search: search || undefined, role: roleFilter || undefined, page, per_page: 15 },
      })
      return res.data
    },
  })

  const { data: roles } = useQuery({
    queryKey: ['roles'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<RoleItem[]>>('/roles')
      return res.data.data
    },
  })

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<UserForm>({
    resolver: zodResolver(userSchema),
  })

  const createMutation = useMutation({
    mutationFn: (formData: UserForm) => api.post('/users', formData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] })
      reset()
      setShowForm(false)
    },
  })

  const deleteMutation = useMutation({
    mutationFn: (id: string) => api.delete(`/users/${id}`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['users'] }),
  })

  const onSubmit = (formData: UserForm) => {
    createMutation.mutate(formData)
  }

  const roleBadgeColor: Record<string, string> = {
    super_admin: 'bg-red-100 text-red-700',
    hr_admin: 'bg-purple-100 text-purple-700',
    div_manager: 'bg-blue-100 text-blue-700',
    supervisor: 'bg-green-100 text-green-700',
    staff: 'bg-gray-100 text-gray-700',
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
        <button
          onClick={() => { setShowForm(!showForm); setEditingUser(null); reset() }}
          className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
        >
          {showForm ? 'Tutup Form' : 'Tambah User'}
        </button>
      </div>

      {/* Create Form */}
      {showForm && (
        <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {editingUser ? 'Edit User' : 'Tambah User Baru'}
          </h2>
          <form onSubmit={handleSubmit(onSubmit)} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
              <input
                {...register('name')}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 dark:text-white"
              />
              {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
              <input
                type="email"
                {...register('email')}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 dark:text-white"
              />
              {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
              <input
                type="password"
                {...register('password')}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 dark:text-white"
              />
              {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
              <select
                {...register('role')}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Role</option>
                {roles?.map((r) => (
                  <option key={r.id} value={r.name}>{r.name}</option>
                ))}
              </select>
              {errors.role && <p className="mt-1 text-xs text-red-600">{errors.role.message}</p>}
            </div>
            <div className="md:col-span-2 flex gap-2">
              <button
                type="submit"
                disabled={createMutation.isPending}
                className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {createMutation.isPending ? 'Menyimpan...' : 'Simpan'}
              </button>
              {createMutation.isError && (
                <p className="text-sm text-red-600 self-center">
                  {(createMutation.error as any)?.response?.data?.message || 'Gagal menyimpan'}
                </p>
              )}
            </div>
          </form>
        </div>
      )}

      {/* Filters */}
      <div className="flex gap-4">
        <input
          type="text"
          placeholder="Cari nama/email..."
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPage(1) }}
          className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm w-64 bg-white dark:bg-gray-700 dark:text-white"
        />
        <select
          value={roleFilter}
          onChange={(e) => { setRoleFilter(e.target.value); setPage(1) }}
          className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 dark:text-white"
        >
          <option value="">Semua Role</option>
          {roles?.map((r) => (
            <option key={r.id} value={r.name}>{r.name}</option>
          ))}
        </select>
      </div>

      {/* Table */}
      <div className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                  <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Nama</th>
                  <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Email</th>
                  <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Role</th>
                  <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Karyawan</th>
                  <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Login Terakhir</th>
                  <th className="text-center py-3 px-4 text-gray-600 dark:text-gray-300 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody>
                {data?.data?.map((user) => (
                  <tr key={user.id} className="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td className="py-3 px-4 text-gray-900 dark:text-white font-medium">{user.name}</td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">{user.email}</td>
                    <td className="py-3 px-4">
                      {user.roles.map((role) => (
                        <span
                          key={role}
                          className={`inline-block px-2 py-0.5 rounded-full text-xs font-medium ${roleBadgeColor[role] || 'bg-gray-100 text-gray-700'}`}
                        >
                          {role}
                        </span>
                      ))}
                    </td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">
                      {user.employee?.full_name || <span className="text-gray-400">-</span>}
                    </td>
                    <td className="py-3 px-4 text-gray-600 dark:text-gray-400">
                      {user.last_login_at ? formatDateTime(user.last_login_at) : '-'}
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => {
                          if (confirm(`Hapus user "${user.name}"?`)) {
                            deleteMutation.mutate(user.id)
                          }
                        }}
                        className="text-red-600 hover:text-red-800 text-xs font-medium"
                      >
                        Hapus
                      </button>
                    </td>
                  </tr>
                ))}
                {(!data?.data || data.data.length === 0) && (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-gray-500">Tidak ada data user.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}

        {/* Pagination */}
        {data?.meta && data.meta.last_page > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            <p className="text-sm text-gray-600 dark:text-gray-400">
              Hal {data.meta.current_page} dari {data.meta.last_page} ({data.meta.total} user)
            </p>
            <div className="flex gap-2">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page <= 1}
                className="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
              >
                Prev
              </button>
              <button
                onClick={() => setPage((p) => p + 1)}
                disabled={page >= (data?.meta?.last_page ?? 1)}
                className="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md disabled:opacity-50 hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-white"
              >
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
