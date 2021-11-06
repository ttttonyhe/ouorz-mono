import React from 'react'
import Icons from '~/components/Icons'
import { getApi } from '~/assets/utilities/Api'

const SubscriptionBox = ({ type }: { type: string }) => {
	const [email, setEmail] = React.useState<string>('')
	const [subscribed, setSubscribed] = React.useState<boolean>(false)
	const [processing, setProcessing] = React.useState<boolean>(false)

	const doSubscribe = async () => {
		setProcessing(true)

		const data = await fetch(getApi({ subs: true }), {
			method: 'post',
			headers: {
				'Content-Type': 'application/json',
				Authorization: process.env.NEXT_PUBLIC_BUTTONDOWN_TOKEN,
			},
			body: JSON.stringify({ email: email, tags: ['Blog Newsletter'] }),
		})
			.then((res) => res.json())
			.finally(() => setProcessing(false))

		if (data.creation_date) {
			setSubscribed(true)
		} else {
			alert('An error has occurred, please try again')
		}
	}

	if (type === 'sm') {
		return (
			<div className="border shadow-sm w-full py-3 px-5 hidden lg:flex rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2 space-x-4">
				<div>
					<p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap items-center flex">
						<span className="w-7 h-7 mr-2">{Icons.subscribe}</span>Get post
						updates straight to your inbox
					</p>
				</div>
				<div className="flex justify-end w-full">
					{subscribed ? (
						<div className="bg-green-500 w-10/12 py-1.5 text-4 rounded-md text-center text-white">
							Succeed
						</div>
					) : (
						<input
							type="email"
							value={email}
							className={`${
								processing ? 'animate-pulse' : ''
							} text-4 px-4 h-8 focus:outline-none w-10/12 shadow-sm rounded-md border bg-white dark:bg-gray-700 dark:border-gray-700 text-gray-500 dark:text-gray-400 tracking-wide flex justify-items-center`}
							placeholder="Email address"
							onChange={(e) => {
								setEmail(e.target.value)
							}}
							onKeyPress={(e) => {
								if (e.key === 'Enter') {
									doSubscribe()
								}
							}}
						/>
					)}
				</div>
			</div>
		)
	} else {
		return (
			<div className="border shadow-sm w-full p-10 lg:py-11 lg:px-20 rounded-xl bg-white dark:bg-gray-800 dark:border-gray-800 items-center my-2 lg:block hidden">
				<div>
					<h1 className="flex text-3xl font-medium text-gray-700 dark:text-white tracking-wide items-center">
						<span className="w-9 h-9 mr-2">{Icons.subscribe}</span>Subscribe
					</h1>
					<p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 mt-2 mb-5">
						Get post updates straight to your inbox
					</p>
				</div>
				<div className="w-full grid grid-cols-3 gap-5">
					<div className="col-start-1 col-end-3 w-full grid grid-cols-3 rounded-md bg-white dark:bg-gray-800 dark:border-gray-800 text-gray-600 dark:text-gray-400 tracking-wide">
						<input
							type="email"
							value={email}
							className="col-start-1 col-end-3 w-full font-light border-r-0 rounded-tl-md rounded-bl-md px-4 py-2 focus:outline-none shadow-sm border border-gray-200 dark:border-gray-500 focus:border-gray-300 dark:bg-gray-600"
							placeholder="Email address"
							onChange={(e) => {
								setEmail(e.target.value)
							}}
							onKeyPress={(e) => {
								if (e.key === 'Enter') {
									doSubscribe()
								}
							}}
						/>
						{subscribed ? (
							<div className="bg-green-500 border border-green-600 cursor-pointer shadow-sm col-start-3 col-end-4 rounded-tr-md rounded-br-md text-center text-green-50 flex items-center">
								<span className="mx-auto">Succeed</span>
							</div>
						) : (
							<div
								className="bg-blue-50 border border-blue-200 dark:border-blue-400 dark:bg-blue-500 hover:bg-blue-100 hover:border-blue-300 cursor-pointer shadow-sm col-start-3 col-end-4 rounded-tr-md rounded-br-md text-center text-blue-500 dark:text-white flex items-center"
								onClick={() => {
									doSubscribe()
								}}
							>
								<span className="mx-auto">
									{processing ? 'Processing...' : 'Subscribe'}
								</span>
							</div>
						)}
					</div>
					<a
						href="https://discord.gg/TTwGnMgcxr"
						target="_blank"
						rel="noreferrer"
						className="flex text-indigo-700 dark:text-indigo-50 col-start-3 col-end-4 border-indigo-200 dark:border-indigo-400 dark:bg-indigo-500 hover:border-indigo-300 hover:bg-indigo-100 border text-center bg-indigo-50 rounded-md shadow-sm items-center justify-center"
					>
						<i className="w-5 h-5 mr-1.5">{Icons.chatRounded}</i> Discord Server
					</a>
				</div>
			</div>
		)
	}
}

export default SubscriptionBox
