import { NavLink } from 'react-router-dom'
import { useAuthStore } from '@/stores/authStore'
import { useUiStore } from '@/stores/uiStore'
import { cn } from '@/lib/utils'

const navigation = [
  { name: 'Dashboard', href: '/dashboard', permission: null },
  { name: 'Karyawan', href: '/employees', permission: 'employees.view' },
  { name: 'Absensi', href: '/attendance', permission: 'attendance.view' },
  { name: 'Cuti', href: '/leave', permission: 'leave.view' },
  { name: 'Dokumen', href: '/documents', permission: 'documents.view' },
  { name: 'Shift', href: '/shifts', permission: 'shifts.view' },
  { name: 'Approval Cuti', href: '/leave/approvals', permission: 'leave.approve' },
  { name: 'Laporan Absensi', href: '/reports/attendance', permission: 'reports.view' },
  { name: 'IP Whitelist', href: '/settings/ip-whitelist', permission: 'ip_whitelist.view' },
  { name: 'Feature Flags', href: '/settings/feature-flags', permission: 'feature_flags.view' },
  { name: 'Users', href: '/settings/users', permission: 'users.view' },
]

export default function Sidebar() {
  const hasPermission = useAuthStore((s) => s.hasPermission)
  const sidebarOpen = useUiStore((s) => s.sidebarOpen)

  const visibleNav = navigation.filter(
    (item) => !item.permission || hasPermission(item.permission)
  )

  return (
    <aside
      className={cn(
        'fixed left-0 top-0 z-40 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-transform',
        sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full'
      )}
    >
      <div className="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-700">
        <span className="text-lg font-bold text-gray-900 dark:text-white">HR System</span>
      </div>

      <nav className="p-4 space-y-1">
        {visibleNav.map((item) => (
          <NavLink
            key={item.href}
            to={item.href}
            className={({ isActive }) =>
              cn(
                'block px-3 py-2 rounded-md text-sm font-medium transition-colors',
                isActive
                  ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'
              )
            }
          >
            {item.name}
          </NavLink>
        ))}
      </nav>
    </aside>
  )
}
