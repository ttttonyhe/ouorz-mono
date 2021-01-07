import ContentLoader from 'react-content-loader'
import { useTheme } from 'next-themes'
import { useEffect, useState } from 'react'

export default function CardSkeleton() {
  const [mounted, setMounted] = useState(false)
  const { resolvedTheme } = useTheme()

  useEffect(() => setMounted(true), [])

  if (!mounted) return null
  return (
    <div className="w-full p-10 shadow-sm bg-white dark:bg-gray-800 dark:border-gray-800 rounded-md border mb-6 text-center">
      {/* @ts-ignore */}
      <ContentLoader
        speed={1}
        width={100}
        style={{ width: '100%' }}
        height={100}
        backgroundColor={resolvedTheme === 'light' ? '#f3f3f3' : '#374151'}
        foregroundColor={resolvedTheme === 'light' ? '#ecebeb' : '#4B5563'}
      >
        <rect x="0" y="0" rx="5" ry="5" width="31%" height="100" />
        <rect x="34%" y="0" rx="5" ry="5" width="95%" height="30" />
        <rect x="34%" y="41" rx="2" ry="2" width="60%" height="15" />
        <rect x="34%" y="63" rx="2" ry="2" width="50%" height="15" />
        <rect x="34%" y="85" rx="2" ry="2" width="55%" height="15" />
      </ContentLoader>
    </div>
  )
}
