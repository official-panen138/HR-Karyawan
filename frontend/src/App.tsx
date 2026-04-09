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
import AttendanceIndex from '@/pages/attendance/Index'
import AttendanceReport from '@/pages/attendance/Report'
import LeaveIndex from '@/pages/leave/Index'
import LeaveCreate from '@/pages/leave/Create'
import LeaveApprovals from '@/pages/leave/Approvals'
import DocumentIndex from '@/pages/documents/Index'
import ShiftIndex from '@/pages/shifts/Index'
import IpWhitelist from '@/pages/settings/IpWhitelist'
import FeatureFlags from '@/pages/settings/FeatureFlags'
import Users from '@/pages/settings/Users'
import ReportAttendance from '@/pages/reports/Attendance'

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

            {/* Attendance */}
            <Route path="/attendance" element={<AttendanceIndex />} />
            <Route path="/attendance/report" element={<AttendanceReport />} />

            {/* Leave */}
            <Route path="/leave" element={<LeaveIndex />} />
            <Route path="/leave/create" element={<LeaveCreate />} />
            <Route path="/leave/approvals" element={<LeaveApprovals />} />

            {/* Documents */}
            <Route path="/documents" element={<DocumentIndex />} />

            {/* Shifts */}
            <Route path="/shifts" element={<ShiftIndex />} />

            {/* Reports */}
            <Route path="/reports/attendance" element={<ReportAttendance />} />

            {/* Settings */}
            <Route path="/settings/ip-whitelist" element={<IpWhitelist />} />
            <Route path="/settings/feature-flags" element={<FeatureFlags />} />
            <Route path="/settings/users" element={<Users />} />
          </Route>

          {/* Redirect root to dashboard */}
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}
