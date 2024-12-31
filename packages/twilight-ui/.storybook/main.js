import { dirname, join } from "path"
import TsconfigPathsPlugin from "tsconfig-paths-webpack-plugin"

/** @type { import('@storybook/react-webpack5').StorybookConfig } */
const config = {
	staticDirs: ["./public"],
	stories: ["../stories/**/*.stories.ts", "../src/**/*.stories.@(mdx|tsx)"],
	addons: [
		// Support Tailwind CSS
		{
			name: "@storybook/addon-essentials",
			options: {
				actions: false,
				backgrounds: false,
				controls: true,
				docs: true,
				toolbars: true,
				viewport: true,
				outline: false,
				measure: false,
			},
		},
		{
			name: "@storybook/addon-styling",
			options: {
				postCss: true,
			},
		},
		getAbsolutePath("@storybook/addon-links"),
		getAbsolutePath("@storybook/addon-storysource"),
		getAbsolutePath("storybook-dark-mode"),
		getAbsolutePath("storybook-addon-turbo-build"),
		getAbsolutePath("@storybook/addon-webpack5-compiler-babel"),
	],
	// Automatically generate docs for controls
	typescript: {
		check: false,
		checkOptions: {},
		reactDocgen: "react-docgen-typescript",
		reactDocgenTypescriptOptions: {
			shouldExtractLiteralValuesFromEnum: true,
			propFilter: (prop) =>
				prop.parent ? !/node_modules/.test(prop.parent.fileName) : true,
		},
	},
	framework: {
		name: getAbsolutePath("@storybook/react-webpack5"),
		options: {
			builder: {
				lazyCompilation: false,
				fsCache: true,
			},
		},
	},
	// Resolve paths in tsconfig
	webpackFinal: async (config) => {
		config.resolve.plugins = [
			...(config.resolve.plugins || []),
			new TsconfigPathsPlugin({
				extensions: config.resolve.extensions,
			}),
		]
		return config
	},
	docs: {
		defaultName: "Documentation",
	},
}

export default config

function getAbsolutePath(value) {
	return dirname(require.resolve(join(value, "package.json")))
}
