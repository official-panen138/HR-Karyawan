import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClientProvider } from '@tanstack/react-query'
import { queryClient } from '@/lib/queryClient'
import AppLayout from '@/components/layout/AppLayout'
import Login from '@/pages/auth/Login'
import Dashboard from '@/pages/dashboard/Dashboard'

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          {/* Auth */}
          <Route path="/login" element={<Login />} />

          {/* Protected routes */}
          <Route element={<AppLayout />}>
            <Route path="/dashboard" element={<Dashboard />} />

            {/* Placeholder routes — akan diisi di Fase 2 */}
            {/* <Route path="/employees" element={<EmployeeIndex />} /> */}
            {/* <Route path="/attendance" element={<AttendanceIndex />} /> */}
            {/* <Route path="/leave" element={<LeaveIndex />} /> */}
          </Route>

          {/* Redirect root to dashboard */}
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
