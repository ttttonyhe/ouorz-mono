import React from "react"
import { Tooltip } from "react-tooltip"
import Dot from "components/common/Dot"
import styles from "./ChartTooltip.module.css"

export default function ChartTooltip({ chartId, tooltip }) {
	if (!tooltip) {
		return null
	}

	const { title, value, label, labelColor } = tooltip

	return (
		<Tooltip id={`${chartId}-tooltip`} isOpen float>
			<div className={styles.tooltip}>
				<div className={styles.content}>
					<div className={styles.title}>{title}</div>
					<div className={styles.metric}>
						<Dot color={labelColor} />
						{value} {label}
					</div>
				</div>
			</div>
		</Tooltip>
	)
}
