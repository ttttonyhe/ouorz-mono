import React from 'react'

type Props = {
	fallback: React.ReactNode
	children: React.ReactNode
}

type State = {
	hasError: boolean
	error: any
}

class ErrorBoundary extends React.Component<Props, State> {
	state = { hasError: false, error: null }

	static getDerivedStateFromError(error: any) {
		return {
			hasError: true,
			error,
		}
	}

	render() {
		if (this.state.hasError) {
			return this.props.fallback
		}
		return this.props.children
	}
}

export default ErrorBoundary
