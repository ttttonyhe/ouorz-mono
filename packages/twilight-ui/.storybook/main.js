import TsconfigPathsPlugin from "tsconfig-paths-webpack-plugin"

/** @type { import('@storybook/react-webpack5').StorybookConfig } */
const config = {
	staticDirs: ["./public"],
	stories: ["../stories/**/*.stories.ts", "../src/**/*.stories.@(mdx|tsx)"],
	addons: [
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
		}, // Support Tailwind CSS
		{
			name: "@storybook/addon-styling",
			options: {
				postCss: true,
			},
		},
		"@storybook/addon-links",
		"@storybook/addon-storysource",
		"storybook-dark-mode",
		"storybook-addon-turbo-build",
		"@storybook/addon-themes",
		"@storybook/addon-webpack5-compiler-swc",
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
		name: "@storybook/react-webpack5",
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
