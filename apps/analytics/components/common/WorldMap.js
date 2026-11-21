import React, { useEffect, useMemo, useState } from "react"
import { useRouter } from "next/router"
import { Tooltip } from "react-tooltip"
import { geoMercator, geoPath } from "d3-geo"
import classNames from "classnames"
import { colord } from "colord"
import useTheme from "hooks/useTheme"
import useCountryNames from "hooks/useCountryNames"
import useLocale from "hooks/useLocale"
import { ISO_COUNTRIES, THEME_COLORS, MAP_FILE } from "lib/constants"
import styles from "./WorldMap.module.css"

const MAP_WIDTH = 800
const MAP_HEIGHT = 500

function WorldMap({ data, className }) {
	const { basePath } = useRouter()
	const [tooltip, setTooltip] = useState(null)
	const [hoveredCode, setHoveredCode] = useState(null)
	const [features, setFeatures] = useState([])
	const [theme] = useTheme(true)
	const { locale } = useLocale()
	const countryNames = useCountryNames(locale)

	const colors = useMemo(
		() => ({
			baseColor: THEME_COLORS[theme].primary,
			fillColor: THEME_COLORS[theme].gray100,
			strokeColor: THEME_COLORS[theme].primary,
			hoverColor: THEME_COLORS[theme].primary,
		}),
		[theme]
	)

	const pathGenerator = useMemo(() => {
		const projection = geoMercator()
			.scale(110)
			.center([0, 40])
			.translate([MAP_WIDTH / 2, MAP_HEIGHT / 2])
		return geoPath(projection)
	}, [])

	useEffect(() => {
		let cancelled = false
		fetch(`${basePath}${MAP_FILE}`)
			.then((res) => (res.ok ? res.json() : null))
			.then((geo) => {
				if (!cancelled && Array.isArray(geo?.features)) {
					setFeatures(geo.features)
				}
			})
			.catch(() => {})
		return () => {
			cancelled = true
		}
	}, [basePath])

	function getFillColor(code) {
		if (hoveredCode === code) return colors.hoverColor
		const country = data?.find(({ x }) => x === code)

		if (!country) {
			return colors.fillColor
		}

		return colord(colors.baseColor)
			[theme === "light" ? "lighten" : "darken"](0.4 * (1.0 - country.z / 100))
			.toHex()
	}

	function handleHover(code) {
		setHoveredCode(code)
		const country = data?.find(({ x }) => x === code)
		setTooltip(`${countryNames[code] || code}: ${country?.y || 0} visitors`)
	}

	return (
		<div
			className={classNames(styles.container, className)}
			data-tooltip-id="world-map-tooltip"
			data-tooltip-content={tooltip || undefined}
		>
			<svg
				viewBox={`0 0 ${MAP_WIDTH} ${MAP_HEIGHT}`}
				role="img"
				aria-label="World map of visitors by country"
				style={{ width: "100%", height: "auto" }}
			>
				<g>
					{features.map((feature, index) => {
						const code = ISO_COUNTRIES[feature.id]
						const d = pathGenerator(feature)
						if (!d || code === "AQ") return null

						return (
							<path
								key={feature.id || index}
								d={d}
								fill={getFillColor(code)}
								stroke={colors.strokeColor}
								strokeWidth={0.5}
								style={{ outline: "none" }}
								onMouseEnter={() => handleHover(code)}
								onMouseLeave={() => {
									setHoveredCode(null)
									setTooltip(null)
								}}
							/>
						)
					})}
				</g>
			</svg>
			<Tooltip id="world-map-tooltip" float />
		</div>
	)
}

export default WorldMap
