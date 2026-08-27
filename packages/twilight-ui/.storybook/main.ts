import type { StorybookConfig } from "@storybook/react-webpack5"
import TsconfigPathsPlugin from "tsconfig-paths-webpack-plugin"

const config: StorybookConfig = {
	staticDirs: ["./public"],
	stories: ["../stories/**/*.stories.ts", "../src/**/*.stories.@(mdx|tsx)"],
	addons: [
		"@storybook/addon-webpack5-compiler-swc",
		"@storybook/addon-docs",
		"@storybook/addon-links",
		"@storybook/addon-themes",
		"storybook-dark-mode",
		{
			name: "@storybook/addon-styling-webpack",
			options: {
				rules: [
					{
						test: /\.css$/u,
						use: [
							"style-loader",
							{ loader: "css-loader", options: { importLoaders: 1 } },
							"postcss-loader",
						],
					},
				],
			},
		},
	],
	framework: {
		name: "@storybook/react-webpack5",
		options: {
			builder: {
				lazyCompilation: false,
				fsCache: true,
			},
		},
	},
	typescript: {
		check: false,
		reactDocgen: "react-docgen-typescript",
		reactDocgenTypescriptOptions: {
			shouldExtractLiteralValuesFromEnum: true,
			propFilter: (prop) =>
				prop.parent ? !/node_modules/u.test(prop.parent.fileName) : true,
		},
	},
	webpackFinal: (webpackConfig) => {
		webpackConfig.resolve ??= {}
		webpackConfig.resolve.plugins = [
			...(webpackConfig.resolve.plugins ?? []),
			new TsconfigPathsPlugin({ extensions: webpackConfig.resolve.extensions }),
		]
		return webpackConfig
	},
	docs: {
		defaultName: "Documentation",
	},
}

export default config
