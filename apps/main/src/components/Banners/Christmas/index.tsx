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
		<div className="bg-rose-700 text-white justify-center px-4.5 py-3 rounded-md w-full flex gap-x-4 shadow-sm">
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
