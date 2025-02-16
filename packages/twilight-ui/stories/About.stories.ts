import About from "./About"
import mdx from "./About.mdx"

export default {
	title: "Introduction/ About",
	component: About,
	parameters: {
		docs: {
			page: mdx,
		},
		controls: {
			hideNoControlsWarning: true,
		},
	},
}

export { About }
