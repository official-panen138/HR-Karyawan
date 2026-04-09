export interface Attendance {
  id: string
  employee_id: string
  shift_id: string | null
  date: string
  check_in_at: string | null
  check_out_at: string | null
  check_in_ip: string | null
  check_out_ip: string | null
  late_minutes: number
  effective_minutes: number
  status: 'present' | 'late' | 'absent' | 'permission' | 'day_off'
  notes: string | null
}

export interface BreakLog {
  id: string
  attendance_id: string
  category: 'smoke' | 'toilet' | 'go_out'
  started_at: string
  ended_at: string | null
  duration_minutes: number | null
}

export interface Shift {
  id: string
  name: string
  start_time: string
  end_time: string
  crosses_midnight: boolean
  color: string
  is_active: boolean
}
