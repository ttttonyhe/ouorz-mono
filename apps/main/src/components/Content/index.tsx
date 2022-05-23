import Header from '~/components/Header'
import Footer from '../Footer'
import React from 'react'

interface Props {
	children: React.ReactNode
}

export default function Content(props: Props) {
	const { children } = props
	return (
		<div>
			<Header />
			<main className="w-full min-h-main lg:w-content h-auto mx-auto pt-0 lg:pt-20 px-5 lg:px-10">
				{children}
			</main>
			<Footer />
		</div>
	)
}
