import { QueryClient } from '@tanstack/react-query'

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 minute — data dianggap stale setelah 1 menit
      gcTime: 5 * 60 * 1000, // 5 minutes — cache dihapus setelah 5 menit unused
      retry: 1,
      refetchOnWindowFocus: true, // refetch saat user kembali ke tab
    },
  },
})
