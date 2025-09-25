import { captureUnderscoreErrorException } from "@sentry/nextjs"
import { Button } from "@twilight-toolkit/ui"
import type { ErrorProps } from "next/error"
import NextError from "next/error"
import Head from "next/head"
import { pageLayout } from "~/components/Page"
import type { NextPageWithLayout } from "~/pages/_app"

const ErrorPage: NextPageWithLayout = ({ statusCode }: ErrorProps) => {
	return (
		<div>
			<Head>
				<title>Error - Tony He</title>
				<link rel="icon" type="image/x-icon" href="/favicon.ico" />
			</Head>
			<div className="mt-0 flex h-[65vh] items-center justify-center pt-24 lg:mt-20 lg:pt-0">
				<div className="text-center">
					<h1 className="mb-0.5 font-medium text-3xl text-black leading-14 tracking-wide lg:text-1 dark:text-white">
						Oops
					</h1>
					<p className="font-light text-3 text-gray-500 leading-14 tracking-wide lg:text-2">
						{statusCode
							? `An error with code ${statusCode} has occurred on the server`
							: "An error has occurred on the client"}
					</p>
					<div className="mt-4 inline-block justify-center">
						<Button
							type="primary"
							className="mx-auto"
							onClick={() => {
								location.href = "/"
							}}>
							Back to Home
						</Button>
					</div>
				</div>
			</div>
		</div>
	)
}

ErrorPage.layout = pageLayout

ErrorPage.getInitialProps = async (contextData) => {
	await captureUnderscoreErrorException(contextData)
	return NextError.getInitialProps(contextData)
}

export default ErrorPage
