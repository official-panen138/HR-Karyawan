import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/axios'
import type { ApiResponse } from '@/types/api'

interface FeatureFlag {
  id: string
  feature_key: string
  label: string
  is_globally_enabled: boolean
  enabled_for_roles: string[]
}

export default function FeatureFlags() {
  const queryClient = useQueryClient()

  const { data: flags, isLoading } = useQuery({
    queryKey: ['feature-flags'],
    queryFn: async () => {
      const res = await api.get<ApiResponse<FeatureFlag[]>>('/feature-flags')
      return res.data.data
    },
  })

  const toggleMutation = useMutation({
    mutationFn: (id: string) => api.post(`/feature-flags/${id}/toggle`),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['feature-flags'] }),
  })

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">Feature Flags</h1>

      <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
        {isLoading ? (
          <div className="p-8 text-center text-gray-500">Memuat data...</div>
        ) : !flags || flags.length === 0 ? (
          <div className="p-8 text-center text-gray-500">Belum ada feature flag.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-gray-50 border-b border-gray-200">
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Key</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Label</th>
                  <th className="text-left py-3 px-4 text-gray-600 font-medium">Roles</th>
                  <th className="text-center py-3 px-4 text-gray-600 font-medium">Global</th>
                </tr>
              </thead>
              <tbody>
                {flags.map((flag) => (
                  <tr key={flag.id} className="border-b border-gray-100 hover:bg-gray-50">
                    <td className="py-3 px-4 font-mono text-xs">{flag.feature_key}</td>
                    <td className="py-3 px-4">{flag.label}</td>
                    <td className="py-3 px-4">
                      <div className="flex flex-wrap gap-1">
                        {flag.enabled_for_roles.length > 0 ? (
                          flag.enabled_for_roles.map((role) => (
                            <span
                              key={role}
                              className="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs"
                            >
                              {role}
                            </span>
                          ))
                        ) : (
                          <span className="text-gray-400">-</span>
                        )}
                      </div>
                    </td>
                    <td className="py-3 px-4 text-center">
                      <button
                        onClick={() => toggleMutation.mutate(flag.id)}
                        disabled={toggleMutation.isPending}
                        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                          flag.is_globally_enabled ? 'bg-blue-600' : 'bg-gray-300'
                        }`}
                      >
                        <span
                          className={`inline-block h-4 w-4 rounded-full bg-white transition-transform ${
                            flag.is_globally_enabled ? 'translate-x-6' : 'translate-x-1'
                          }`}
                        />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  )
}
