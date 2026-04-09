import { Outlet, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/stores/authStore'
import { useUiStore } from '@/stores/uiStore'
import { useMe } from '@/hooks/useAuth'
import { cn } from '@/lib/utils'
import Sidebar from './Sidebar'
import Header from './Header'

export default function AppLayout() {
  const token = useAuthStore((s) => s.token)
  const sidebarOpen = useUiStore((s) => s.sidebarOpen)
  const { isLoading } = useMe()

  if (!token) {
    return <Navigate to="/login" replace />
  }

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-gray-500">Memuat...</p>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Sidebar />
      <div className={cn('transition-all', sidebarOpen ? 'ml-64' : 'ml-0')}>
        <Header />
        <main className="p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
