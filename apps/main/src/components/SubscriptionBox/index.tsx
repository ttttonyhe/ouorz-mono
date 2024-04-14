import { Icon } from "@twilight-toolkit/ui"
import React from "react"
import getAPI from "~/utilities/api"

const SubscriptionBox = ({ type }: { type: string }) => {
	const [email, setEmail] = React.useState<string>("")
	const [subscribed, setSubscribed] = React.useState<boolean>(false)
	const [processing, setProcessing] = React.useState<boolean>(false)

	const doSubscribe = async () => {
		setProcessing(true)

		const data = await fetch(getAPI("external", "subscribeToButtondown"), {
			method: "post",
			headers: {
				"Content-Type": "application/json",
				Authorization: process.env.NEXT_PUBLIC_BUTTONDOWN_TOKEN,
			},
			body: JSON.stringify({ email: email, tags: ["Blog Newsletter"] }),
		})
			.then((res) => res.json())
			.finally(() => setProcessing(false))

		if (data.creation_date) {
			setSubscribed(true)
		} else {
			alert("An error has occurred, please try again.")
		}
	}

	if (type === "sm") {
		return (
			<div className="my-2 hidden w-full items-center space-x-4 rounded-md border bg-white px-5 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-800 lg:flex">
				<div>
					<p className="flex items-center whitespace-nowrap text-xl tracking-wide text-gray-500 dark:text-gray-400">
						<span className="mr-2 h-7 w-7">
							<Icon name="subscribe" />
						</span>
						New Article Everytime I Publish :)
					</p>
				</div>
				<div className="flex w-full justify-end">
					{subscribed ? (
						<div className="w-10/12 rounded-md bg-green-500 py-1.5 text-center text-4 text-white">
							Done!
						</div>
					) : (
						<input
							type="email"
							value={email}
							className={`${
								processing ? "animate-pulse" : ""
							} flex h-8 w-10/12 justify-items-center rounded-md border bg-white px-4 text-4 tracking-wide text-gray-500 shadow-sm focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400`}
							placeholder="Email address"
							onChange={(e) => {
								setEmail(e.target.value)
							}}
							onKeyDown={(e) => {
								if (e.key === "Enter") {
									doSubscribe()
								}
							}}
						/>
					)}
				</div>
			</div>
		)
	}

	return (
		<div className="my-2 hidden w-full items-center rounded-xl border bg-white p-10 shadow-sm dark:border-gray-800 dark:bg-gray-800 lg:block lg:px-20 lg:py-11">
			<div className="flex justify-between">
				<div>
					<h1 className="flex items-center text-3xl font-medium tracking-wide text-gray-700 dark:text-white">
						<span className="mr-2 h-9 w-9">
							<Icon name="subscribe" />
						</span>
						Subscribe
					</h1>
					<p className="mb-5 mt-1 pl-1 text-xl tracking-wide text-gray-500 dark:text-gray-400">
						New Article Everytime I Publish :)
					</p>
				</div>
				<div className="flex items-center">
					<a href="https://www.ouorz.com/feed" target="_blank" rel="noreferrer">
						<button className="effect-pressing -mt-4.5 flex w-full cursor-pointer items-center justify-center gap-x-1 rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xl tracking-wider text-gray-500 shadow-sm hover:shadow-inner focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
							<span className="h-6 w-6">
								<Icon name="rss" />
							</span>
							RSS
						</button>
					</a>
				</div>
			</div>
			<div className="grid w-full grid-cols-3 gap-5">
				<div className="col-start-1 col-end-3 grid w-full grid-cols-3 rounded-md bg-white tracking-wide text-gray-600 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
					<input
						type="email"
						value={email}
						className="col-start-1 col-end-3 w-full rounded-bl-md rounded-tl-md border border-r-0 border-gray-200 px-4 py-2 font-light shadow-sm focus:border-gray-300 focus:outline-none dark:border-gray-500 dark:bg-gray-600"
						placeholder="Email address"
						onChange={(e) => {
							setEmail(e.target.value)
						}}
						onKeyDown={(e) => {
							if (e.key === "Enter") {
								doSubscribe()
							}
						}}
					/>
					{subscribed ? (
						<div className="col-start-3 col-end-4 flex cursor-pointer items-center rounded-br-md rounded-tr-md border border-green-600 bg-green-500 text-center text-green-50 shadow-sm">
							<span className="mx-auto">Succeed</span>
						</div>
					) : (
						<button
							className="col-start-3 col-end-4 flex cursor-pointer items-center rounded-br-md rounded-tr-md border border-blue-200 bg-blue-50 text-center text-blue-500 shadow-sm hover:border-blue-300 hover:bg-blue-100 dark:border-blue-400 dark:bg-blue-500 dark:text-white dark:hover:border-blue-400 dark:hover:bg-blue-600"
							onClick={() => {
								email && doSubscribe()
							}}>
							<span className="mx-auto">
								{processing ? "Processing..." : "Subscribe"}
							</span>
						</button>
					)}
				</div>
				<a
					href="https://discord.gg/TTwGnMgcxr"
					target="_blank"
					rel="noreferrer"
					className="col-start-3 col-end-4 flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 text-center text-indigo-700 shadow-sm hover:border-indigo-300 hover:bg-indigo-100 dark:border-indigo-400 dark:bg-indigo-500 dark:text-indigo-50 dark:hover:border-indigo-400 dark:hover:bg-indigo-600">
					<i className="mr-1.5 h-5 w-5">
						<Icon name="chatRounded" />
					</i>{" "}
					Discord Server
				</a>
			</div>
		</div>
	)
}

export default SubscriptionBox
