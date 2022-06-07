const TsconfigPathsPlugin = require('tsconfig-paths-webpack-plugin')

module.exports = {
	stories: ['../stories/**/*.stories.ts', '../src/**/*.stories.@(mdx|tsx)'],
	addons: [
		'@storybook/addon-docs',
		'@storybook/addon-links',
		'@storybook/addon-essentials',
		'@storybook/addon-a11y',
		'storybook-dark-mode',
		// Support Tailwind CSS
		{
			name: '@storybook/addon-postcss',
			options: {
				postcssLoaderOptions: {
					implementation: require('postcss'),
				},
			},
		},
	],
	// Automatically generate docs for controls
	typescript: {
		check: false,
		checkOptions: {},
		reactDocgen: 'react-docgen-typescript',
		reactDocgenTypescriptOptions: {
			shouldExtractLiteralValuesFromEnum: true,
			propFilter: (prop) =>
				prop.parent ? !/node_modules/.test(prop.parent.fileName) : true,
		},
	},
	features: {
		// Enable code splitting
		storyStoreV7: true,
	},
	framework: '@storybook/react',
	core: {
		builder: {
			name: '@storybook/builder-webpack5',
			options: {
				// Webpack features
				lazyCompilation: true,
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
}
