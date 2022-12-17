import styled from 'styled-components'
import { useTheme } from 'next-themes'

interface GlowingBackgroundProps {
	rounded?: 'sm' | 'md' | 'xl'
}

interface GlowingDivBackgroundProps extends GlowingBackgroundProps {
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

const borderRadius = (props: GlowingDivBackgroundProps) => {
	switch (props.rounded) {
		case 'sm':
			return '0.125rem'
		case 'md':
			return '0.375rem'
		case 'xl':
			return '0.75rem'
	}
}

const GlowingDivBackground = styled.div`
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
	background: ${background};
	background-color: ${backgroundColor};
	contain: strict;
	transition: opacity 400ms ease 0s;
`

const GlowingBackground = ({ rounded }: GlowingBackgroundProps) => {
	// FIXME: useTheme is not working with styled-components in SSR mode
	const { resolvedTheme } = useTheme()

	return (
		<GlowingDivBackground
			rounded={rounded || 'md'}
			resolvedTheme={resolvedTheme || 'dark'}
		/>
	)
}

export default GlowingBackground
