import { useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { useMutation } from '@tanstack/react-query'
import { z } from 'zod'
import api from '@/lib/axios'
import { DIVISION_OPTIONS, POSITION_OPTIONS, RELIGION_OPTIONS } from '@/pages/employees/constants'

const employeeSchema = z.object({
  full_name: z.string().min(1, 'Nama lengkap wajib diisi'),
  religion: z.string().min(1, 'Agama wajib dipilih'),
  birth_date: z.string().min(1, 'Tanggal lahir wajib diisi'),
  bank_account: z.string().optional(),
  bank_name: z.string().optional(),
  joined_at: z.string().min(1, 'Tanggal bergabung wajib diisi'),
  initial_salary: z.string().min(1, 'Gaji awal wajib diisi'),
  current_salary: z.string().min(1, 'Gaji saat ini wajib diisi'),
  position_id: z.string().min(1, 'Posisi wajib dipilih'),
  division_id: z.string().min(1, 'Divisi wajib dipilih'),
})

type EmployeeFormData = z.infer<typeof employeeSchema>

export default function EmployeeCreate() {
  const navigate = useNavigate()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<EmployeeFormData>({
    resolver: zodResolver(employeeSchema),
  })

  const mutation = useMutation({
    mutationFn: (data: EmployeeFormData) => api.post('/employees', data),
    onSuccess: () => navigate('/employees'),
  })

  const onSubmit = (data: EmployeeFormData) => {
    mutation.mutate(data)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Tambah Karyawan</h1>

      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          {mutation.isError && (
            <div className="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
              Gagal menyimpan data. Silakan coba lagi.
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Field label="Nama Lengkap" error={errors.full_name?.message}>
              <input
                type="text"
                {...register('full_name')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Agama" error={errors.religion?.message}>
              <select
                {...register('religion')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Pilih Agama</option>
                {RELIGION_OPTIONS.map((r) => (
                  <option key={r.value} value={r.value}>{r.label}</option>
                ))}
              </select>
            </Field>

            <Field label="Tanggal Lahir" error={errors.birth_date?.message}>
              <input
                type="date"
                {...register('birth_date')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Tanggal Bergabung" error={errors.joined_at?.message}>
              <input
                type="date"
                {...register('joined_at')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Divisi" error={errors.division_id?.message}>
              <select
                {...register('division_id')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Pilih Divisi</option>
                {DIVISION_OPTIONS.map((d) => (
                  <option key={d.value} value={d.value}>{d.label}</option>
                ))}
              </select>
            </Field>

            <Field label="Posisi" error={errors.position_id?.message}>
              <select
                {...register('position_id')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Pilih Posisi</option>
                {POSITION_OPTIONS.map((p) => (
                  <option key={p.value} value={p.value}>{p.label}</option>
                ))}
              </select>
            </Field>

            <Field label="Gaji Awal" error={errors.initial_salary?.message}>
              <input
                type="number"
                {...register('initial_salary')}
                placeholder="0"
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Gaji Saat Ini" error={errors.current_salary?.message}>
              <input
                type="number"
                {...register('current_salary')}
                placeholder="0"
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Nama Bank" error={errors.bank_name?.message}>
              <input
                type="text"
                {...register('bank_name')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>

            <Field label="Nomor Rekening" error={errors.bank_account?.message}>
              <input
                type="text"
                {...register('bank_account')}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </Field>
          </div>

          <div className="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              onClick={() => navigate('/employees')}
              className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              disabled={mutation.isPending}
              className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {mutation.isPending ? 'Menyimpan...' : 'Simpan'}
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
      <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
      {children}
      {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
    </div>
  )
}
