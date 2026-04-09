import type { ReactNode } from 'react'

export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="w-full max-w-md p-8">
        <div className="text-center mb-8">
          <h1 className="text-2xl font-bold text-gray-900">HR Management System</h1>
          <p className="text-gray-500 mt-1">Kelola SDM perusahaan Anda</p>
        </div>
        {children}
      </div>
    </div>
  )
}
