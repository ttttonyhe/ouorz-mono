import React from "react"
import { colord } from "colord"
import classNames from "classnames"
import Dot from "components/common/Dot"
import useLocale from "hooks/useLocale"
import useForceUpdate from "hooks/useForceUpdate"
import styles from "./Legend.module.css"

export default function Legend({ chart }) {
	const { locale } = useLocale()
	const forceUpdate = useForceUpdate()

	function handleClick(index) {
		chart.setDatasetVisibility(index, !chart.isDatasetVisible(index))
		chart.update()
		forceUpdate()
	}

	const items = chart?.legend?.legendItems
	if (!items?.some(({ text }) => text)) {
		return null
	}

	return (
		<div className={styles.legend}>
			{items.map(({ text, fillStyle, datasetIndex, hidden }) => {
				const color = colord(fillStyle)

				return (
					<div
						key={text}
						className={classNames(styles.label, { [styles.hidden]: hidden })}
						onClick={() => handleClick(datasetIndex)}
					>
						<Dot color={color.alpha(color.alpha() + 0.2).toHex()} />
						<span className={locale}>{text}</span>
					</div>
				)
			})}
		</div>
	)
}
