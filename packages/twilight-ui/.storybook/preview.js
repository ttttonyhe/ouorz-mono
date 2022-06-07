// Tailwind and custom CSS
import '../styles/vendor.css'
import '../styles/base.css'

export const parameters = {
	actions: { argTypesRegex: '^on[A-Z].*' },
	controls: {
		// Show full documentation for the controls
		expanded: true,
		matchers: {
			color: /(background|color)$/i,
			date: /Date$/,
		},
	},
	// Add a dark mode switch to toolbar
	darkMode: {
		classTarget: 'html',
		stylePreview: true,
		darkClass: 'dark',
		lightClass: 'light',
	},
}
