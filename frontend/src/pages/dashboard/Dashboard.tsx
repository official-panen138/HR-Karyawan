import { useQuery } from '@tanstack/react-query'
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
  PieChart, Pie, Cell,
} from 'recharts'
import api from '@/lib/axios'
import { useAuthStore } from '@/stores/authStore'

interface DashboardStats {
  total_employees: number
  present_today: number
  pending_leaves: number
  expiring_documents: number
  expired_documents: number
}

const weeklyData = [
  { day: 'Sen', present: 42, late: 3, absent: 5 },
  { day: 'Sel', present: 44, late: 2, absent: 4 },
  { day: 'Rab', present: 40, late: 5, absent: 5 },
  { day: 'Kam', present: 43, late: 4, absent: 3 },
  { day: 'Jum', present: 38, late: 2, absent: 10 },
]

const leaveData = [
  { name: 'Approved', value: 24, color: '#22C55E' },
  { name: 'Pending', value: 8, color: '#F59E0B' },
  { name: 'Rejected', value: 3, color: '#EF4444' },
]

export default function Dashboard() {
  const user = useAuthStore((s) => s.user)

  const { data: stats } = useQuery({
    queryKey: ['dashboard', 'stats'],
    queryFn: async () => {
      const res = await api.get<{ success: boolean; data: DashboardStats }>('/dashboard/stats')
      return res.data.data
    },
  })

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h1>

      {/* Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard title="Total Karyawan" value={stats?.total_employees ?? '-'} color="blue" />
        <StatCard title="Hadir Hari Ini" value={stats?.present_today ?? '-'} color="green" />
        <StatCard title="Cuti Pending" value={stats?.pending_leaves ?? '-'} color="yellow" />
        <StatCard title="Dokumen Expiring" value={stats?.expiring_documents ?? '-'} color="red" />
      </div>

      {/* Welcome */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
          Selamat datang, {user?.name}
        </h2>
        <p className="text-gray-500 dark:text-gray-400">
          Role: <span className="font-medium text-gray-700 dark:text-gray-300">{user?.roles?.[0]}</span>
        </p>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Bar Chart - Attendance Overview */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Ringkasan Kehadiran Mingguan
          </h3>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={weeklyData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="day" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Bar dataKey="present" name="Hadir" fill="#22C55E" />
              <Bar dataKey="late" name="Terlambat" fill="#F59E0B" />
              <Bar dataKey="absent" name="Absen" fill="#EF4444" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Pie Chart - Leave Status */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Distribusi Status Cuti
          </h3>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={leaveData}
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={100}
                dataKey="value"
                label={({ name, value }) => `${name}: ${value}`}
              >
                {leaveData.map((entry) => (
                  <Cell key={entry.name} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}

function StatCard({ title, value, color }: { title: string; value: string | number; color: string }) {
  const colorMap: Record<string, string> = {
    blue: 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300',
    green: 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300',
    yellow: 'bg-yellow-50 border-yellow-200 text-yellow-700 dark:bg-yellow-900/30 dark:border-yellow-800 dark:text-yellow-300',
    red: 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300',
  }

  return (
    <div className={`rounded-lg border p-4 ${colorMap[color]}`}>
      <p className="text-sm font-medium opacity-75">{title}</p>
      <p className="text-2xl font-bold mt-1">{value}</p>
    </div>
  )
}
