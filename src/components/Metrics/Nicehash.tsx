import useSWR from 'swr'
import fetcher from '~/lib/fetcher'
import Icons from '~/components/Icons'

export default function NiceHashMetric() {
	const { data } = useSWR('api/nicehash', fetcher)

	const profitability = data?.profitability.toFixed(4).toString()
	const unpaidAmount = data?.unpaidAmount.toFixed(2).toString()
	const temperature = parseInt(data?.temperature).toFixed(1).toString()
	const load = parseInt(data?.load).toFixed(1).toString()
	const status = data?.status
	const link = 'https://www.nicehash.com/my/dashboard'

	return (
		<div
			onClick={() => navigateTo(link)}
			className="dark:bg-gray-800 dark:border-gray-800 rounded-md border shadow-sm hover:shadow-md py-4 px-5 bg-white cursor-pointer"
			style={{ borderBottom: '5px solid #F59E0B' }}
		>
			<h1
				className={`font-bold text-stats tracking-wide flex items-center -mb-0.5 ${
					!data && 'animate-pulse'
				}`}
			>
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
							'NaN'
						)
					) : (
						'- - -'
					)}{' '}
					{data && (
						<em className="flex items-center text-sm font-medium not-italic text-gray-500 rounded-md px-2 mt-0.5 py-0.5 ml-2 bg-gray-100 border">
							{data && (
								<span className="w-4.5 h-4.5 mr-1">{Icons['money']}</span>
							)}{' '}
							{unpaidAmount ? unpaidAmount : 0}
							<span className="ml-2 text-xs font-normal">
								x10<sup>-5</sup>
							</span>
						</em>
					)}
				</span>
			</h1>
			{data && (
				<p className="flex items-center mb-2 text-base">
					<span
						className={`border-r pr-3 mr-3 flex items-center font-medium ${
							status ? 'text-green-700' : 'text-red-700'
						} `}
					>
						<span className="w-4.5 h-4.5 mr-1 animate-pulse">
							{Icons[status ? 'checkCircle' : 'warningCircle']}
						</span>
						{status ? 'Online' : 'Offline'}
					</span>
					<span className="border-r pr-3 mr-3">
						{temperature ? temperature : 0} °C
					</span>
					<span className="mr-3">{load ? load : 0} %</span>
				</p>
			)}
			<p className="flex items-center text-gray-500 dark:text-gray-400 tracking-wide overflow-hidden overflow-ellipsis whitespace-nowrap">
				Bitcoin Mining →
			</p>
		</div>
	)
}

const navigateTo = (link) => {
	window.open(link)
}
