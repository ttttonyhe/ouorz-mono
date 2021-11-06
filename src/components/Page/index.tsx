import Header from '~/components/Header'
import Footer from '../Footer'
import React from 'react'

interface Props {
	children: React.ReactNode
}

export default function Page(props: Props) {
	const { children } = props
	return (
		<div>
			<Header />
			<main className="w-full min-h-main lg:w-page h-auto mx-auto pt-0 lg:pt-24">
				{children}
			</main>
			<Footer />
		</div>
	)
}
