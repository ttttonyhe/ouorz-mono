import { useTheme } from "next-themes"
import type React from "react"
import ScrollWrapper from "./scroll"

interface Props {
	componentRef: React.MutableRefObject<HTMLDivElement>
	children: React.ReactNode
}

const BoxShadowTransition = (props: Props) => {
	const { componentRef: ref, children } = props
	const { resolvedTheme } = useTheme()

	const handler = (position: number) => {
		if (!ref?.current) return
		if (resolvedTheme === "dark") {
			ref.current.style.background = `rgba(38, 38, 38, ${
				position * (0.8 / 40) || 0
			})`
			ref.current.style.borderBottom = `1px solid rgba(255, 255, 255, ${
				position * (0.1 / 40) || 0
			})`
		} else {
			ref.current.style.background = `rgba(255, 255, 255, ${
				position * (1 / 40) || 0
			})`
			ref.current.style.boxShadow = `0px 1px 3px rgba(0,0,0,${
				position * (0.08 / 40) || 0
			})`
		}
	}

	return (
		<ScrollWrapper handler={handler} endPosition={40}>
			{children}
		</ScrollWrapper>
	)
}

export default BoxShadowTransition
