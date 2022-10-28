import React from 'react'

interface ContentProps {
	children: React.ReactNode
}

const Content = ({ children }: ContentProps) => {
	return (
		<main className="w-full min-h-main lg:w-page h-auto mx-auto pt-0 lg:pt-24">
			{children}
		</main>
	)
}

export default Content
