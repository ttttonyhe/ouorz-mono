'use client'

import ContentLoader from 'react-content-loader'
import { useTheme } from 'next-themes'

const PageLoading = () => {
	const { resolvedTheme } = useTheme()

	return (
		<article className="lg:shadow-sm lg:border lg:rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 p-5 lg:p-20 pt-24">
			<ContentLoader
				className={resolvedTheme === undefined ? 'opacity-50' : ''}
				uniqueKey="page-loading-skeleton"
				speed={2}
				width={100}
				style={{ width: '100%' }}
				height={500}
				backgroundColor={resolvedTheme === 'dark' ? '#525252' : '#f3f3f3'}
				foregroundColor={resolvedTheme === 'dark' ? '#737373' : '#ecebeb'}
				title=""
			>
				<rect x="0" y="8" rx="5" ry="5" width="35%" height="28" />
				<rect x="0" y="53" rx="5" ry="5" width="75%" height="15" />
				<rect x="0" y="138" rx="5" ry="5" width="65%" height="16" />
				<rect x="0" y="169" rx="5" ry="5" width="85%" height="16" />
				<rect x="0" y="200" rx="5" ry="5" width="100%" height="16" />
				<rect x="0" y="231" rx="5" ry="5" width="100%" height="16" />
				<rect x="0" y="262" rx="5" ry="5" width="100%" height="16" />
				<rect x="0" y="293" rx="5" ry="5" width="100%" height="16" />
				<rect x="0" y="324" rx="5" ry="5" width="100%" height="16" />
			</ContentLoader>
		</article>
	)
}

export default PageLoading
