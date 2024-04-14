import Footer from "../Footer"
import React, { PropsWithChildren } from "react"
import Header from "~/components/Header"

interface Props extends PropsWithChildren<{}> {}

const Content = (props: Props) => {
	const { children } = props
	return (
		<div>
			<Header />
			<main className="mx-auto h-auto min-h-main w-full pt-0 lg:w-page lg:pt-24">
				<>{children}</>
			</main>
			<Footer />
		</div>
	)
}

export const contentLayout = (page: React.ReactElement) => {
	return <Content>{page}</Content>
}

export default Content
