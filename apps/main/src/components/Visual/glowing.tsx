import { styled } from "styled-components"

interface GlowingBackgroundProps {
	$rounded?: "sm" | "md" | "xl"
}

// styled component props resolvers
const borderRadius = (props: GlowingBackgroundProps) => {
	switch (props.$rounded) {
		case "sm":
			return "0.125rem"
		case "md":
			return "0.375rem"
		case "xl":
			return "0.75rem"
	}
}

// Keyed on the `dark` class because `useTheme` is unknown during SSR
const GlowingDivBackground = styled.div<GlowingBackgroundProps>`
	border-radius: ${borderRadius};
	pointer-events: none;
	user-select: none;
	position: absolute;
	z-index: 1;
	opacity: 1;
	top: 1px;
	bottom: 1px;
	left: 1px;
	right: 1px;
	contain: strict;
	transition: opacity 400ms ease 0s;

	.dark & {
		background: radial-gradient(
			200px circle at var(--x-px) var(--y-px),
			rgba(255, 255, 255, 0.1),
			transparent
		);
		background-color: rgb(38, 38, 38);
	}
`

const GlowingBackground = ({
	rounded,
}: {
	rounded?: GlowingBackgroundProps[keyof GlowingBackgroundProps]
}) => {
	return <GlowingDivBackground $rounded={rounded || "md"} />
}

export default GlowingBackground
