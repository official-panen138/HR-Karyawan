import { useAuthStore } from '@/stores/authStore'

export default function Dashboard() {
  const user = useAuthStore((s) => s.user)

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard title="Total Karyawan" value="-" color="blue" />
        <StatCard title="Hadir Hari Ini" value="-" color="green" />
        <StatCard title="Cuti Pending" value="-" color="yellow" />
        <StatCard title="Dokumen Expired" value="-" color="red" />
      </div>

      <div className="bg-white rounded-lg border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-2">Selamat datang, {user?.name}</h2>
        <p className="text-gray-500">
          Role: <span className="font-medium text-gray-700">{user?.roles?.[0]}</span>
        </p>
        <p className="text-gray-400 text-sm mt-4">
          Dashboard lengkap akan ditampilkan setelah modul Fase 2 dibangun.
        </p>
      </div>
    </div>
  )
}

function StatCard({ title, value, color }: { title: string; value: string; color: string }) {
  const colorMap: Record<string, string> = {
    blue: 'bg-blue-50 border-blue-200 text-blue-700',
    green: 'bg-green-50 border-green-200 text-green-700',
    yellow: 'bg-yellow-50 border-yellow-200 text-yellow-700',
    red: 'bg-red-50 border-red-200 text-red-700',
  }

  return (
    <div className={`rounded-lg border p-4 ${colorMap[color]}`}>
      <p className="text-sm font-medium opacity-75">{title}</p>
      <p className="text-2xl font-bold mt-1">{value}</p>
    </div>
  )
}
