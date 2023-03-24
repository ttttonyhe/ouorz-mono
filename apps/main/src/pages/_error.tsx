import Head from 'next/head'
import React from 'react'
import { Button } from '@twilight-toolkit/ui'
import { captureException, flush } from '@sentry/nextjs'
import { NextPageWithLayout } from '~/pages/_app'
import { pageLayout } from '~/components/Page'

interface Props {
	statusCode: number
}

const ErrorPage: NextPageWithLayout = ({ statusCode }: Props) => {
	return (
		<div>
			<Head>
				<title>Error - TonyHe</title>
				<link rel="icon" type="image/x-icon" href="/favicon.ico" />
			</Head>
			<div className="lg:mt-20 mt-0 lg:pt-0 pt-24 justify-center">
				<div className="text-center">
					<h1 className="font-medium text-3xl leading-14 lg:text-1 text-black dark:text-white tracking-wide mb-0.5">
						Oops
					</h1>
					<p className="text-3 lg:text-2 text-gray-500 leading-14 tracking-wide font-light">
						{statusCode
							? `An error with code ${statusCode} has occurred on the server`
							: 'An error has occurred on the client'}
					</p>
					<div className="inline-block justify-center mt-4">
						<Button
							type="primary"
							className="mx-auto"
							onClick={() => {
								location.href = '/'
							}}
						>
							Back to Home
						</Button>
					</div>
				</div>
			</div>
		</div>
	)
}

ErrorPage.layout = pageLayout

ErrorPage.getInitialProps = async ({ res, err }) => {
	const statusCode = res ? res.statusCode : err ? err.statusCode : 404

	if (err) {
		captureException(err)
		await flush(2000)
	}

	return { statusCode }
}

export default ErrorPage
