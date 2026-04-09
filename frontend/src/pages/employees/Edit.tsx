import { useNavigate, useParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation, useQuery } from '@tanstack/react-query'
import { z } from 'zod'
import api from '@/lib/axios'
import { queryClient } from '@/lib/queryClient'
import type { Employee } from '@/types/employee'
import type { ApiResponse } from '@/types/api'
import { RELIGION_OPTIONS } from '@/pages/employees/constants'

interface SelectOption {
  id: string
  name: string
}

const employeeSchema = z.object({
  full_name: z.string().min(1, 'Nama lengkap wajib diisi'),
  religion: z.string().min(1, 'Agama wajib dipilih'),
  birth_date: z.string().min(1, 'Tanggal lahir wajib diisi'),
  bank_account: z.string().optional(),
  bank_name: z.string().optional(),
  joined_at: z.string().min(1, 'Tanggal bergabung wajib diisi'),
  initial_salary: z.coerce.number().min(0),
  current_salary: z.coerce.number().min(0),
  position_id: z.string().min(1, 'Posisi wajib dipilih'),
  division_id: z.string().min(1, 'Divisi wajib dipilih'),
})

type EmployeeFormData = z.infer<typeof employeeSchema>

export default function EmployeeEdit() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()

  const { data: employeeData, isLoading } = useQuery<ApiResponse<Employee>>({
    queryKey: ['employees', id],
    queryFn: async () => {
      const { data } = await api.get(`/employees/${id}`)
      return data
    },
    enabled: !!id,
  })

  const employee = employeeData?.data

  const { data: divisions } = useQuery({
    queryKey: ['divisions'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<SelectOption[]>>('/divisions')
      return res.data.data
    },
  })

  const {
    register,
    handleSubmit,
    watch,
    formState: { errors },
  } = useForm<EmployeeFormData>({
    resolver: zodResolver(employeeSchema),
    values: employee
      ? {
          full_name: employee.full_name,
          religion: employee.religion as string,
          birth_date: employee.birth_date,
          bank_account: employee.bank_account ?? '',
          bank_name: employee.bank_name ?? '',
          joined_at: employee.joined_at,
          initial_salary: parseFloat(employee.initial_salary),
          current_salary: parseFloat(employee.current_salary),
          position_id: employee.position_id,
          division_id: employee.division_id,
        }
      : undefined,
  })

  const selectedDivision = watch('division_id')

  const { data: positions } = useQuery({
    queryKey: ['positions', selectedDivision],
    queryFn: async () => {
      const res = await api.get<ApiResponse<SelectOption[]>>('/positions', {
        params: { division_id: selectedDivision || undefined },
      })
      return res.data.data
    },
  })

  const mutation = useMutation({
    mutationFn: (data: EmployeeFormData) => api.put(`/employees/${id}`, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employees'] })
      navigate('/employees')
    },
  })

  const onSubmit = (data: EmployeeFormData) => {
    mutation.mutate(data)
  }

  if (isLoading) {
    return <div className="flex items-center justify-center py-12"><p className="text-gray-500">Memuat data...</p></div>
  }

  if (!employee) {
    return <div className="flex items-center justify-center py-12"><p className="text-gray-500">Karyawan tidak ditemukan.</p></div>
  }

  const serverErrors = (mutation.error as any)?.response?.data

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">Edit Karyawan</h1>

      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          {mutation.isError && (
            <div className="rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-300">
              <p className="font-medium">{serverErrors?.message || 'Gagal menyimpan data.'}</p>
              {serverErrors?.errors && (
                <ul className="mt-2 list-disc list-inside">
                  {Object.values(serverErrors.errors).flat().map((err: any, i: number) => (
                    <li key={i}>{err}</li>
                  ))}
                </ul>
              )}
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Field label="Nama Lengkap" error={errors.full_name?.message}>
              <input type="text" {...register('full_name')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Agama" error={errors.religion?.message}>
              <select {...register('religion')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Pilih Agama</option>
                {RELIGION_OPTIONS.map((r) => (
                  <option key={r.value} value={r.value}>{r.label}</option>
                ))}
              </select>
            </Field>

            <Field label="Tanggal Lahir" error={errors.birth_date?.message}>
              <input type="date" {...register('birth_date')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Tanggal Bergabung" error={errors.joined_at?.message}>
              <input type="date" {...register('joined_at')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Divisi" error={errors.division_id?.message}>
              <select {...register('division_id')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Pilih Divisi</option>
                {divisions?.map((d) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
            </Field>

            <Field label="Posisi" error={errors.position_id?.message}>
              <select {...register('position_id')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Pilih Posisi</option>
                {positions?.map((p) => (
                  <option key={p.id} value={p.id}>{p.name}</option>
                ))}
              </select>
            </Field>

            <Field label="Gaji Awal" error={errors.initial_salary?.message}>
              <input type="number" {...register('initial_salary')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Gaji Saat Ini" error={errors.current_salary?.message}>
              <input type="number" {...register('current_salary')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Nama Bank" error={errors.bank_name?.message}>
              <input type="text" {...register('bank_name')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>

            <Field label="Nomor Rekening" error={errors.bank_account?.message}>
              <input type="text" {...register('bank_account')} className="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm dark:text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            </Field>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onClick={() => navigate('/employees')} className="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
              Batal
            </button>
            <button type="submit" disabled={mutation.isPending} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
              {mutation.isPending ? 'Menyimpan...' : 'Simpan Perubahan'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
  return (
    <div>
      <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{label}</label>
      {children}
      {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
    </div>
  )
}
