import "../styles/vendor.css"
import "../styles/base.css"
import { withThemeByClassName } from "@storybook/addon-themes"
import type { Preview } from "@storybook/react-webpack5"
import { themes } from "storybook/theming"

const brand = {
	brandTitle: "@twilight-toolkit/ui",
	brandUrl: "https://ui.twilight-toolkit.ouorz.com",
}

const preview: Preview = {
	parameters: {
		controls: {
			expanded: true,
			matchers: {
				color: /(background|color)$/iu,
				date: /Date$/u,
			},
		},
		darkMode: {
			dark: { ...themes.dark, ...brand },
			light: { ...themes.normal, ...brand },
		},
	},
	decorators: [
		withThemeByClassName({
			themes: {
				light: "",
				dark: "dark",
			},
			defaultTheme: "light",
		}),
	],
}

export default preview
