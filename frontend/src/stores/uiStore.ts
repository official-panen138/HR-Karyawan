import { create } from 'zustand'

interface UiState {
  sidebarOpen: boolean
  darkMode: boolean
  toggleSidebar: () => void
  setSidebarOpen: (open: boolean) => void
  toggleDarkMode: () => void
}

export const useUiStore = create<UiState>((set) => ({
  sidebarOpen: true,
  darkMode: (() => {
    const saved = localStorage.getItem('dark_mode') === 'true'
    if (saved) {
      document.documentElement.classList.add('dark')
    }
    return saved
  })(),
  toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
  setSidebarOpen: (open) => set({ sidebarOpen: open }),
  toggleDarkMode: () =>
    set((state) => {
      const next = !state.darkMode
      localStorage.setItem('dark_mode', String(next))
      if (next) {
        document.documentElement.classList.add('dark')
      } else {
        document.documentElement.classList.remove('dark')
      }
      return { darkMode: next }
    }),
}))
