import { useEffect } from 'react'
import { useMutation, useQuery } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import api from '@/lib/axios'
import { useAuthStore } from '@/stores/authStore'
import type { ApiResponse } from '@/types/api'
import type { AuthResponse, User } from '@/types/employee'

export function useLogin() {
  const setAuth = useAuthStore((s) => s.setAuth)
  const navigate = useNavigate()

  return useMutation({
    mutationFn: async (credentials: { email: string; password: string }) => {
      const { data } = await api.post<ApiResponse<AuthResponse>>('/login', credentials)
      return data.data
    },
    onSuccess: (data) => {
      setAuth(data.user, data.token)
      navigate('/dashboard')
    },
  })
}

export function useLogout() {
  const clearAuth = useAuthStore((s) => s.clearAuth)
  const navigate = useNavigate()

  return useMutation({
    mutationFn: async () => {
      await api.post('/logout')
    },
    onSettled: () => {
      clearAuth()
      navigate('/login')
    },
  })
}

export function useMe() {
  const token = useAuthStore((s) => s.token)
  const setAuth = useAuthStore((s) => s.setAuth)

  const query = useQuery({
    queryKey: ['me'],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<User>>('/me')
      return data.data
    },
    enabled: !!token,
    staleTime: 30 * 1000, // 30 seconds — refetch user data more often
    retry: 1,
  })

  // Sync fetched user data to Zustand store
  useEffect(() => {
    if (query.data && token) {
      setAuth(query.data, token)
    }
  }, [query.data, token, setAuth])

  return query
}
