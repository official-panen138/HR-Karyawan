import { Link, useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import api from '@/lib/axios'
import { formatDate, formatRupiah } from '@/lib/utils'
import type { Employee } from '@/types/employee'
import type { ApiResponse } from '@/types/api'

export default function EmployeeShow() {
  const { id } = useParams<{ id: string }>()

  const { data, isLoading } = useQuery<ApiResponse<Employee>>({
    queryKey: ['employees', id],
    queryFn: async () => {
      const { data } = await api.get(`/employees/${id}`)
      return data
    },
    enabled: !!id,
  })

  const employee = data?.data

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-gray-500">Memuat data...</p>
      </div>
    )
  }

  if (!employee) {
    return (
      <div className="flex items-center justify-center py-12">
        <p className="text-gray-500">Karyawan tidak ditemukan.</p>
      </div>
    )
  }

  const statusBadge: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    inactive: 'bg-gray-100 text-gray-700',
    resigned: 'bg-yellow-100 text-yellow-700',
    terminated: 'bg-red-100 text-red-700',
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Detail Karyawan</h1>
        <div className="flex gap-3">
          <Link
            to="/employees"
            className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Kembali
          </Link>
          <Link
            to={`/employees/${id}/edit`}
            className="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600"
          >
            Edit
          </Link>
        </div>
      </div>

      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
          <InfoRow label="Nama Lengkap" value={employee.full_name} />
          <InfoRow
            label="Status"
            value={
              <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${statusBadge[employee.status] ?? 'bg-gray-100 text-gray-700'}`}>
                {employee.status}
              </span>
            }
          />
          <InfoRow label="Agama" value={employee.religion} />
          <InfoRow label="Tanggal Lahir" value={formatDate(employee.birth_date)} />
          <InfoRow label="Divisi" value={employee.division?.name ?? '-'} />
          <InfoRow label="Posisi" value={employee.position?.name ?? '-'} />
          <InfoRow label="Tanggal Bergabung" value={formatDate(employee.joined_at)} />
          <InfoRow label="Gaji Awal" value={formatRupiah(employee.initial_salary)} />
          <InfoRow label="Gaji Saat Ini" value={formatRupiah(employee.current_salary)} />
          <InfoRow label="Nama Bank" value={employee.bank_name ?? '-'} />
          <InfoRow label="Nomor Rekening" value={employee.bank_account ?? '-'} />
        </div>
      </div>
    </div>
  )
}

function InfoRow({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="py-2 border-b border-gray-100">
      <dt className="text-xs font-medium text-gray-500 uppercase tracking-wider">{label}</dt>
      <dd className="mt-1 text-sm text-gray-900">{value}</dd>
    </div>
  )
}
