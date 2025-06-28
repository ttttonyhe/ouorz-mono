import React, { useEffect } from "react"
import SnowFall from "react-snowfall"

const ChirstmasBanner = () => {
	const [showSnow, setShowSnow] = React.useState(true)
	useEffect(() => {
		setTimeout(() => {
			setShowSnow(false)
		}, 10000)
	}, [])

	return (
		<div className="flex w-full justify-center gap-x-4 rounded-md bg-rose-700 px-4.5 py-3 text-white shadow-xs">
			{showSnow && <SnowFall />}
			<div>
				<i>🎄</i>
			</div>
			<div>
				<p>Merry Christmas</p>
			</div>
		</div>
	)
}

export default ChirstmasBanner
