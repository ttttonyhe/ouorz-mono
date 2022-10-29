import { ReactNode, ReactElement } from 'react'
import Header from '~/components/Header'
import Footer from '~/components/Footer'

interface PageProps {
	children: ReactNode
}

const Page = ({ children }: PageProps) => {
	return (
		<div className="w-full min-h-main lg:w-content h-auto mx-auto pt-0 lg:pt-20 px-5 lg:px-10">
			{children}
		</div>
	)
}

export const pageLayout = (page: ReactElement) => {
	return (
		<div>
			<Header />
			<main className="w-full min-h-main lg:w-content h-auto mx-auto pt-0 lg:pt-20 px-5 lg:px-10">
				{page}
			</main>
			<Footer />
		</div>
	)
}

export default Page
