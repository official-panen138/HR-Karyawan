export interface LeaveType {
  id: string
  code: string
  name: string
  default_quota_days: number | null
  is_paid: boolean
  requires_document: boolean
  is_active: boolean
}

export interface LeaveRequest {
  id: string
  employee_id: string
  leave_type_id: string
  start_date: string
  end_date: string
  total_days: number
  reason: string | null
  status: 'draft' | 'pending' | 'partially_approved' | 'approved' | 'rejected' | 'cancelled'
  is_emergency: boolean
  submitted_at: string | null
  leave_type?: LeaveType
}

export interface LeaveBalance {
  id: string
  employee_id: string
  leave_type_id: string
  year: number
  quota_days: number
  carry_over_days: number
  used_days: number
  pending_days: number
  remaining_days: number
  leave_type?: LeaveType
}
