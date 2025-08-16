import { Icon } from "@twilight-toolkit/ui"
import useSWR from "swr"
import fetcher from "~/lib/fetcher"

export default function NiceHashMetric() {
	const { data } = useSWR("api/nicehash", fetcher)

	const profitability = data?.profitability.toFixed(4).toString()
	const unpaidAmount = data?.unpaidAmount.toFixed(2).toString()
	const temperature = parseInt(data?.temperature).toFixed(1).toString()
	const load = parseInt(data?.load).toFixed(1).toString()
	const status = data?.status
	const link = "https://www.nicehash.com/my/dashboard"

	return (
		<div
			onClick={() => navigateTo(link)}
			className="shadow-xs cursor-pointer rounded-md border bg-white px-5 py-4 hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
			style={{ borderBottom: "5px solid #F59E0B" }}>
			<h1
				className={`-mb-0.5 flex items-center text-stats font-bold tracking-wide ${
					!data && "animate-pulse"
				}`}>
				<span className="flex items-center">
					{data ? (
						status ? (
							<>
								{profitability}
								<span className="ml-1 mt-1 text-base font-normal">
									x10<sup>-5</sup>
								</span>
							</>
						) : (
							"NaN"
						)
					) : (
						"- - -"
					)}{" "}
					{data && (
						<em className="ml-2 mt-0.5 flex items-center rounded-md border bg-gray-100 px-2 py-0.5 text-sm font-medium not-italic text-gray-500">
							{data && (
								<span className="mr-1 h-4.5 w-4.5">
									<Icon name="money" />
								</span>
							)}{" "}
							{unpaidAmount ? unpaidAmount : 0}
							<span className="ml-2 text-xs font-normal">
								x10<sup>-5</sup>
							</span>
						</em>
					)}
				</span>
			</h1>
			{data && (
				<p className="mb-2 flex items-center text-base">
					<span
						className={`mr-3 flex items-center border-r pr-3 font-medium ${
							status ? "text-green-700" : "text-red-700"
						} `}>
						<span className="mr-1 h-4.5 w-4.5 animate-pulse">
							<Icon name={status ? "checkCircle" : "warningCircle"} />
						</span>
						{status ? "Online" : "Offline"}
					</span>
					<span className="mr-3 border-r pr-3">
						{temperature ? temperature : 0} °C
					</span>
					<span className="mr-3">{load ? load : 0} %</span>
				</p>
			)}
			<p className="flex items-center overflow-hidden text-ellipsis whitespace-nowrap tracking-wide text-gray-500 dark:text-gray-400">
				Bitcoin Mining →
			</p>
		</div>
	)
}

const navigateTo = (link) => {
	window.open(link)
}
