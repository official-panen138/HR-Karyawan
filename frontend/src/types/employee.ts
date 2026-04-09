export interface Division {
  id: string
  name: string
  code: string
  description: string | null
  is_active: boolean
}

export interface Position {
  id: string
  name: string
  code: string
  division_id: string | null
  level: number
  is_active: boolean
}

export interface Employee {
  id: string
  full_name: string
  religion: string
  birth_date: string
  bank_account: string | null
  bank_name: string | null
  joined_at: string
  initial_salary: string
  current_salary: string
  position_id: string
  division_id: string
  status: 'active' | 'inactive' | 'resigned' | 'terminated'
  photo_path: string | null
  position?: Position
  division?: Division
}

export interface User {
  id: string
  name: string
  email: string
  employee_id: string | null
  employee?: Employee | null
  roles: string[]
  permissions: string[]
}

export interface AuthResponse {
  user: User
  token: string
}
