'use client'

import { usePathname } from 'next/navigation'
import Headings from '~/constants/headings'

const PageDefaultHead = () => {
	const pathname = usePathname()
	const headingData = Headings[pathname]
	const title = headingData ? `${headingData.title} - TonyHe` : 'TonyHe'

	return (
		<>
			<title>{title}</title>
			<link
				rel="icon"
				href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📊</text></svg>"
			/>
			<meta name="description" content="TonyHe's personal dashboard" />
			<meta name="robots" content="noindex" />
		</>
	)
}

export default PageDefaultHead
