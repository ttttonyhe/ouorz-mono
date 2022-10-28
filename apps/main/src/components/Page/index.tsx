import React from 'react'

interface PageProps {
	children: React.ReactNode
}

const Page = ({ children }: PageProps) => {
	return (
		<div className="w-full min-h-main lg:w-content h-auto mx-auto pt-0 lg:pt-20 px-5 lg:px-10">
			{children}
		</div>
	)
}

export default Page
