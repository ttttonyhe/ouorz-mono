import React, { useState, useRef, useEffect } from "react"
import classNames from "classnames"
import {
	Chart as ChartJS,
	BarController,
	BarElement,
	CategoryScale,
	LinearScale,
	TimeScale,
	Tooltip,
	Legend as ChartLegend,
} from "chart.js"
import "chartjs-adapter-date-fns"
import Legend from "components/metrics/Legend"
import { formatLongNumber } from "lib/format"
import { dateFormat } from "lib/date"
import useLocale from "hooks/useLocale"
import useTheme from "hooks/useTheme"
import useForceUpdate from "hooks/useForceUpdate"
import { DEFAULT_ANIMATION_DURATION, THEME_COLORS } from "lib/constants"
import styles from "./BarChart.module.css"
import ChartTooltip from "./ChartTooltip"

ChartJS.register(
	BarController,
	BarElement,
	CategoryScale,
	LinearScale,
	TimeScale,
	Tooltip,
	ChartLegend
)

export default function BarChart({
	chartId,
	datasets,
	unit,
	records,
	animationDuration = DEFAULT_ANIMATION_DURATION,
	className,
	stacked = false,
	loading = false,
	onCreate = () => {},
	onUpdate = () => {},
}) {
	const canvas = useRef(null)
	const chart = useRef(null)
	const [tooltip, setTooltip] = useState(null)
	const { locale } = useLocale()
	const [theme] = useTheme(true)
	const forceUpdate = useForceUpdate()

	const colors = {
		text: THEME_COLORS[theme].gray700,
		line: THEME_COLORS[theme].gray200,
		zeroLine: THEME_COLORS[theme].gray500,
	}

	function renderXLabel(value, index, ticks) {
		if (loading || !canvas.current) return ""
		const tickValue = ticks[index]?.value ?? value
		const d = new Date(tickValue)
		const sw = canvas.current.width / (window.devicePixelRatio || 1)

		switch (unit) {
			case "minute":
				return index % 2 === 0 ? dateFormat(d, "H:mm", locale) : ""
			case "hour":
				return dateFormat(d, "p", locale)
			case "day":
				if (records > 25) {
					if (sw <= 275) {
						return index % 10 === 0 ? dateFormat(d, "M/d", locale) : ""
					}
					if (sw <= 550) {
						return index % 5 === 0 ? dateFormat(d, "M/d", locale) : ""
					}
					if (sw <= 700) {
						return index % 2 === 0 ? dateFormat(d, "M/d", locale) : ""
					}
					return dateFormat(d, "MMM d", locale)
				}
				if (sw <= 375) {
					return index % 2 === 0 ? dateFormat(d, "MMM d", locale) : ""
				}
				if (sw <= 425) {
					return dateFormat(d, "MMM d", locale)
				}
				return dateFormat(d, "EEE M/d", locale)
			case "month":
				if (sw <= 330) {
					return index % 2 === 0 ? dateFormat(d, "MMM", locale) : ""
				}
				return dateFormat(d, "MMM", locale)
			default:
				return String(value)
		}
	}

	function renderYLabel(label) {
		return +label > 1000 ? formatLongNumber(label) : label
	}

	function getTooltipFormat(activeUnit) {
		switch (activeUnit) {
			case "hour":
				return "EEE p — PPP"
			default:
				return "PPPP"
		}
	}

	function renderTooltip({ tooltip: model }) {
		const { opacity, title, body, labelColors } = model

		if (!opacity || !title?.length || !body?.length) {
			setTooltip(null)
			return
		}

		const [label, value] = body[0].lines[0].split(":")

		setTooltip({
			title: dateFormat(new Date(+title[0]), getTooltipFormat(unit), locale),
			value,
			label,
			labelColor: labelColors[0]?.backgroundColor,
		})
	}

	function createChart() {
		const options = {
			parsing: {
				xAxisKey: "t",
				yAxisKey: "y",
			},
			animation: {
				duration: animationDuration,
			},
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					display: false,
				},
				tooltip: {
					enabled: false,
					external: renderTooltip,
				},
			},
			scales: {
				x: {
					type: "time",
					time: {
						unit,
						tooltipFormat: "T",
					},
					ticks: {
						callback: renderXLabel,
						minRotation: 0,
						maxRotation: 0,
						color: colors.text,
						autoSkipPadding: 1,
					},
					grid: {
						display: false,
					},
					offset: true,
					stacked: true,
				},
				y: {
					beginAtZero: true,
					ticks: {
						callback: renderYLabel,
						color: colors.text,
						precision: 0,
					},
					grid: {
						color: colors.line,
					},
					border: {
						color: colors.zeroLine,
					},
					stacked,
				},
			},
		}

		onCreate(options)

		chart.current = new ChartJS(canvas.current, {
			type: "bar",
			data: {
				datasets,
			},
			options,
		})
	}

	function updateChart() {
		const { options } = chart.current

		options.scales.x.time.unit = unit
		options.scales.x.ticks.callback = renderXLabel
		options.scales.x.ticks.color = colors.text
		options.scales.y.ticks.color = colors.text
		options.scales.y.ticks.precision = 0
		options.scales.y.grid.color = colors.line
		options.scales.y.border.color = colors.zeroLine
		options.animation.duration = animationDuration
		options.plugins.tooltip.external = renderTooltip

		onUpdate(chart.current)

		chart.current.update()

		forceUpdate()
	}

	useEffect(() => {
		if (datasets && canvas.current) {
			if (!chart.current) {
				createChart()
			} else {
				setTooltip(null)
				updateChart()
			}
		}
	}, [datasets, unit, animationDuration, locale, theme])

	useEffect(
		() => () => {
			chart.current?.destroy()
			chart.current = null
		},
		[]
	)

	return (
		<>
			<div
				data-tooltip-id={`${chartId}-tooltip`}
				className={classNames(styles.chart, className)}
			>
				<canvas ref={canvas} />
			</div>
			<Legend chart={chart.current} />
			<ChartTooltip chartId={chartId} tooltip={tooltip} />
		</>
	)
}
