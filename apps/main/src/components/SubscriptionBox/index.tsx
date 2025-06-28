import { Icon } from "@twilight-toolkit/ui"
import React from "react"
import { LISTMONK_LIST_UUID } from "~/constants/ids"
import getAPI from "~/utilities/api"

const SubscriptionBox = ({
	type,
	className,
}: {
	type: string
	className?: string
}) => {
	const [email, setEmail] = React.useState<string>("")
	const [subscribed, setSubscribed] = React.useState<boolean>(false)
	const [processing, setProcessing] = React.useState<boolean>(false)

	const doSubscribe = async () => {
		if (processing) return

		setProcessing(true)

		const data = await fetch(getAPI("external", "subscribeToListmonk"), {
			method: "post",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				email: email,
				name: email.split("@")[0],
				// eslint-disable-next-line camelcase
				list_uuids: [LISTMONK_LIST_UUID],
			}),
		})
			.then((res) => res.json())
			.finally(() => setProcessing(false))

		if (data.data) {
			setSubscribed(true)
		} else {
			alert("An error has occurred, please try again.")
		}
	}

	if (type === "sm") {
		return (
			<div
				data-cy="indexNewsletter"
				className="my-2 flex w-full flex-col gap-y-3 rounded-md border bg-white px-4.5 py-4 shadow-xs lg:flex-row lg:items-center lg:gap-x-10 lg:space-x-4 lg:gap-y-0 lg:py-3 dark:border-gray-700 dark:bg-gray-800">
				<div>
					<p className="flex items-center text-xl tracking-wide whitespace-nowrap text-gray-600 dark:text-gray-300">
						<span className="mr-2 h-7 w-7">
							<Icon name="subscribe" />
						</span>
						New Article Everytime I Publish :)
					</p>
				</div>
				<div className="flex w-full justify-end">
					{subscribed ? (
						<div className="text-4 w-full rounded-md bg-green-500 py-1.5 text-center text-white">
							Success!
						</div>
					) : (
						<form
							onSubmit={(e) => {
								e.preventDefault()
								doSubscribe()
							}}
							className="flex w-full">
							<input
								type="email"
								value={email}
								className={`${
									processing ? "animate-pulse border-gray-400 duration-75" : ""
								} text-4 flex h-8 w-full rounded-md border bg-white px-4 tracking-wide text-gray-500 shadow-xs focus:outline-hidden dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400`}
								placeholder="your.email@address.com"
								onChange={(e) => {
									setEmail(e.target.value)
								}}
								onKeyDown={(e) => {
									if (e.key === "Enter") {
										doSubscribe()
									}
								}}
								required
							/>
							{email && email.length <= 23 && (
								<button
									type="submit"
									className="effect-pressing animate-appear absolute top-[6.5px] right-1.5 hidden rounded-full bg-slate-500 px-[8px] py-[2px] text-xs text-white shadow-xs hover:bg-neutral-600 hover:shadow-inner lg:block dark:bg-neutral-800 dark:text-gray-300">
									Subscribe
								</button>
							)}
							<button
								type="submit"
								className="effect-pressing animate-appear absolute top-[6.5px] right-1.5 rounded-full bg-slate-500 px-[8px] py-[2px] text-xs text-white shadow-xs hover:bg-neutral-600 hover:shadow-inner lg:hidden dark:bg-neutral-800 dark:text-gray-300">
								Subscribe
							</button>
						</form>
					)}
				</div>
			</div>
		)
	}

	return (
		<div
			className={`my-2 hidden w-full items-center rounded-xl border bg-white p-10 shadow-xs lg:block lg:px-20 lg:py-11 dark:border-gray-800 dark:bg-gray-800 ${className || ""}`}>
			<div className="flex justify-between">
				<div>
					<h1 className="flex items-center text-3xl font-medium tracking-wide text-gray-700 dark:text-white">
						<span className="mr-2 h-9 w-9">
							<Icon name="subscribe" />
						</span>
						Subscribe
					</h1>
					<p className="mt-1 mb-5 pl-1 text-xl tracking-wide text-gray-500 dark:text-gray-400">
						New Article Everytime I Publish :)
					</p>
				</div>
				<div className="flex items-center">
					<a href="https://lipeng.ac/feed" target="_blank" rel="noreferrer">
						<button className="effect-pressing -mt-4.5 flex w-full cursor-pointer items-center justify-center gap-x-1 rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xl tracking-wider text-gray-500 shadow-xs hover:shadow-inner focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
							<span className="h-6 w-6">
								<Icon name="rss" />
							</span>
							RSS
						</button>
					</a>
				</div>
			</div>
			<div className="grid w-full grid-cols-3 gap-5">
				<form
					onSubmit={(e) => {
						e.preventDefault()
						doSubscribe()
					}}
					className="col-start-1 col-end-3 grid w-full grid-cols-3 rounded-md bg-white tracking-wide text-gray-600 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
					<input
						type="email"
						value={email}
						className="col-start-1 col-end-3 w-full rounded-tl-md rounded-bl-md border border-r-0 border-gray-200 px-4 py-2 font-light shadow-xs focus:border-gray-300 focus:outline-hidden dark:border-gray-500 dark:bg-gray-600"
						placeholder="Email address"
						onChange={(e) => {
							setEmail(e.target.value)
						}}
						onKeyDown={(e) => {
							if (e.key === "Enter") {
								doSubscribe()
							}
						}}
						required
					/>
					{subscribed ? (
						<div className="col-start-3 col-end-4 flex cursor-pointer items-center rounded-tr-md rounded-br-md border border-green-600 bg-green-500 text-center text-green-50 shadow-xs">
							<span className="mx-auto">Success!</span>
						</div>
					) : (
						<button
							type="submit"
							className="col-start-3 col-end-4 flex cursor-pointer items-center rounded-tr-md rounded-br-md border border-blue-200 bg-blue-50 text-center text-blue-500 shadow-xs hover:border-blue-300 hover:bg-blue-100 dark:border-blue-400 dark:bg-blue-500 dark:text-white dark:hover:border-blue-400 dark:hover:bg-blue-600"
							onClick={() => {
								email && doSubscribe()
							}}>
							<span className="mx-auto">
								{processing ? "Processing..." : "Subscribe"}
							</span>
						</button>
					)}
				</form>
				<a
					href="https://discord.gg/JE8bcYezfY"
					target="_blank"
					rel="noreferrer"
					className="col-start-3 col-end-4 flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 text-center text-indigo-700 shadow-xs hover:border-indigo-300 hover:bg-indigo-100 dark:border-indigo-400 dark:bg-indigo-500 dark:text-indigo-50 dark:hover:border-indigo-400 dark:hover:bg-indigo-600">
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
