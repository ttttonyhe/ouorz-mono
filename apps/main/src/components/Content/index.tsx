import { ReactNode, ReactElement } from 'react'
import Header from '~/components/Header'
import Footer from '~/components/Footer'

interface ContentProps {
	children: ReactNode
}

const Content = ({ children }: ContentProps) => {
	return (
		<main className="w-full min-h-main lg:w-page h-auto mx-auto pt-0 lg:pt-24">
			{children}
		</main>
	)
}

export const contentLayout = (content: ReactElement) => {
	return (
		<div>
			<Header />
			<main className="w-full min-h-main lg:w-page h-auto mx-auto pt-0 lg:pt-24">
				{content}
			</main>
			<Footer />
		</div>
	)
}

export default Content
