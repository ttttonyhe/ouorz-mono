interface GlowingBackgroundProps {
	rounded?: "sm" | "md" | "xl"
}

const RADIUS_CLASS = {
	sm: "rounded-xs",
	md: "rounded-md",
	xl: "rounded-xl",
} as const

const GlowingBackground = ({ rounded = "md" }: GlowingBackgroundProps) => (
	<div className={`glowing-div-bg ${RADIUS_CLASS[rounded]}`} />
)

export default GlowingBackground
