import type React from "react"
import type { PropsWithChildren } from "react"
import Header from "~/components/Header"

import Footer from "../Footer"

type Props = PropsWithChildren

const Content = (props: Props) => {
	const { children } = props
	return (
		<div>
			<a
				href="#main-content"
				className="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:text-black focus:shadow-md dark:focus:bg-gray-800 dark:focus:text-white">
				Skip to main content
			</a>
			<Header />
			<main
				id="main-content"
				className="mx-auto h-auto min-h-main w-full pt-0 lg:w-page lg:pt-24">
				{children}
			</main>
			<Footer />
		</div>
	)
}

export const contentLayout = (page: React.ReactElement) => {
	return <Content>{page}</Content>
}

export default Content
