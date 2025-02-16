// Tailwind and custom CSS
import "../styles/vendor.css"
import { themes } from "@storybook/theming"

const theme = {
	brandTitle: "@twilight-toolkit/ui",
	brandUrl: "https://ui.twilight-toolkit.ouorz.com",
}

/** @type { import('@storybook/react-webpack5').Preview } */
const preview = {
	parameters: {
		controls: {
			// Show full documentation for the controls
			expanded: true,
			matchers: {
				color: /(background|color)$/i,
				date: /Date$/,
			},
		},
		darkMode: {
			dark: {
				...themes.dark,
				...theme,
			},
			light: {
				...themes.normal,
				...theme,
			},
		},
	},
}

export default preview
