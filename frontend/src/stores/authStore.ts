import { create } from 'zustand'
import type { User } from '@/types/employee'

interface AuthState {
  user: User | null
  token: string | null
  setAuth: (user: User, token: string) => void
  clearAuth: () => void
  isAuthenticated: () => boolean
  hasRole: (role: string) => boolean
  hasPermission: (permission: string) => boolean
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: localStorage.getItem('auth_token'),

  setAuth: (user, token) => {
    localStorage.setItem('auth_token', token)
    set({ user, token })
  },

  clearAuth: () => {
    localStorage.removeItem('auth_token')
    set({ user: null, token: null })
  },

  isAuthenticated: () => !!get().token,

  hasRole: (role) => {
    return get().user?.roles.includes(role) ?? false
  },

  hasPermission: (permission) => {
    return get().user?.permissions.includes(permission) ?? false
  },
}))
