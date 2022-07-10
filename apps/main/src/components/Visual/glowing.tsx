import styled from 'styled-components'
import { useTheme } from 'next-themes'

// styled component props
type GlowingDivBackgroundProps = {
	resolvedTheme: string
}

// styled component props resolvers
const background = (props: GlowingDivBackgroundProps) => {
	return (
		props.resolvedTheme === 'dark' &&
		`radial-gradient(200px circle at var(--x-px) var(--y-px), rgba(255, 255, 255, 0.1), transparent)`
	)
}
const backgroundColor = (props: GlowingDivBackgroundProps) => {
	return props.resolvedTheme === 'dark' && 'rgb(38,38,38)'
}

const GlowingBackground = () => {
	const { resolvedTheme } = useTheme()

	// style component
	const GlowingDivBackground = styled.div`
		border-radius: 0.375rem;
		pointer-events: none;
		user-select: none;
		position: absolute;
		z-index: 1;
		opacity: 1;
		top: 1px;
		bottom: 1px;
		left: 1px;
		right: 1px;
		background: ${background};
		background-color: ${backgroundColor};
		contain: strict;
		transition: opacity 400ms ease 0s;
	`
	return <GlowingDivBackground resolvedTheme={resolvedTheme || 'dark'} />
}

export default GlowingBackground
