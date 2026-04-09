import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClientProvider } from '@tanstack/react-query'
import { queryClient } from '@/lib/queryClient'
import AppLayout from '@/components/layout/AppLayout'
import Login from '@/pages/auth/Login'
import Dashboard from '@/pages/dashboard/Dashboard'
import EmployeeIndex from '@/pages/employees/Index'
import EmployeeCreate from '@/pages/employees/Create'
import EmployeeShow from '@/pages/employees/Show'
import EmployeeEdit from '@/pages/employees/Edit'

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

            {/* Employees */}
            <Route path="/employees" element={<EmployeeIndex />} />
            <Route path="/employees/create" element={<EmployeeCreate />} />
            <Route path="/employees/:id" element={<EmployeeShow />} />
            <Route path="/employees/:id/edit" element={<EmployeeEdit />} />

            {/* Placeholder routes — akan diisi di Fase 2 */}
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
