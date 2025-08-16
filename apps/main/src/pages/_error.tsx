import { captureUnderscoreErrorException } from "@sentry/nextjs"
import { Button } from "@twilight-toolkit/ui"
import type { ErrorProps } from "next/error"
import Error from "next/error"
import Head from "next/head"
import React from "react"
import { pageLayout } from "~/components/Page"
import { NextPageWithLayout } from "~/pages/_app"

const ErrorPage: NextPageWithLayout = ({ statusCode }: ErrorProps) => {
	return (
		<div>
			<Head>
				<title>Error - Tony He</title>
				<link rel="icon" type="image/x-icon" href="/favicon.ico" />
			</Head>
			<div className="mt-0 flex h-[65vh] items-center justify-center pt-24 lg:mt-20 lg:pt-0">
				<div className="text-center">
					<h1 className="mb-0.5 text-3xl font-medium leading-14 tracking-wide text-black dark:text-white lg:text-1">
						Oops
					</h1>
					<p className="text-3 font-light leading-14 tracking-wide text-gray-500 lg:text-2">
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
	return Error.getInitialProps(contextData)
}

export default ErrorPage
