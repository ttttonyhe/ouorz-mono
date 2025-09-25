import { Button } from "@twilight-toolkit/ui"
import Head from "next/head"
import { useRouter } from "next/router"
import { pageLayout } from "~/components/Page"
import type { NextPageWithLayout } from "~/pages/_app"

const PageNotFound: NextPageWithLayout = () => {
	const router = useRouter()

	return (
		<div>
			<Head>
				<title>404 - Tony He</title>
				<link rel="icon" type="image/x-icon" href="/favicon.ico" />
			</Head>
			<div className="mt-0 flex h-[65vh] items-center justify-center pt-24 lg:mt-20 lg:pt-0">
				<div className="text-center">
					<h1 className="mb-0.5 font-medium text-3xl text-black leading-14 tracking-wide lg:text-1 dark:text-white">
						Oops
					</h1>
					<p className="font-light text-3 text-gray-500 leading-14 tracking-wide lg:text-2">
						404 Not Found
					</p>
					<div className="mt-4 inline-block justify-center">
						<Button
							type="primary"
							onClick={() => {
								router.push("/")
							}}
							className="mx-auto">
							Back to Home
						</Button>
					</div>
				</div>
			</div>
		</div>
	)
}

PageNotFound.layout = pageLayout

export default PageNotFound
