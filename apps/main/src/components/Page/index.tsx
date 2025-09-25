import type React from "react"
import Header from "~/components/Header"
import Footer from "../Footer"

interface Props {
	children: React.ReactNode
}

export default function Page(props: Props) {
	const { children } = props
	return (
		<div>
			<Header />
			<main className="mx-auto h-auto min-h-main w-full px-5 pt-0 lg:w-content lg:px-10 lg:pt-20">
				{children}
			</main>
			<Footer />
		</div>
	)
}

export const pageLayout = (page: React.ReactElement) => {
	return <Page>{page}</Page>
}
