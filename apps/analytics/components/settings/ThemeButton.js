import React from "react"
import { useTransition, animated } from "react-spring"
import useTheme from "hooks/useTheme"
import Sun from "assets/sun.svg"
import Moon from "assets/moon.svg"
import Gear from "assets/gear.svg"
import styles from "./ThemeButton.module.css"
import Icon from "../common/Icon"

const themes = ["light", "dark", "system"]
const icons = [<Sun key="sun" />, <Moon key="moon" />, <Gear key="gear" />]

export default function ThemeButton() {
	const [theme, setTheme] = useTheme()

	const transitions = useTransition(theme, {
		initial: { opacity: 1 },
		from: {
			opacity: 0,
			transform: "translateY(-20px) scale(0.5)",
		},
		enter: { opacity: 1, transform: "translateY(0px) scale(1)" },
		leave: {
			opacity: 0,
			transform: "translateY(20px) scale(0.5)",
		},
	})

	function handleClick() {
		setTheme(themes[(themes.indexOf(theme) + 1) % themes.length])
	}

	return (
		<div className={styles.button} onClick={handleClick}>
			{transitions((styles, item) => (
				<animated.div key={item} style={styles}>
					<Icon icon={icons[themes.indexOf(item)]} />
				</animated.div>
			))}
		</div>
	)
}
