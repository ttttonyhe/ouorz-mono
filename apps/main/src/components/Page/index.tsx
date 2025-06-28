import Footer from "../Footer"
import React from "react"
import Header from "~/components/Header"

interface Props {
	children: React.ReactNode
}

export default function Page(props: Props) {
	const { children } = props
	return (
		<div>
			<Header />
			<main className="min-h-main lg:w-content mx-auto h-auto w-full px-5 pt-0 lg:px-10 lg:pt-20">
				<>{children}</>
			</main>
			<Footer />
		</div>
	)
}

export const pageLayout = (page: React.ReactElement) => {
	return <Page>{page}</Page>
}
