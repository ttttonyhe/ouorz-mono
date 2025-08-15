module.exports = {
	trailingComma: "es5",
	tabWidth: 2,
	semi: false,
	singleQuote: false,
	bracketSameLine: true,
	printWidth: 80,
	useTabs: true,
	endOfLine: "lf",
	overrides: [
		{
			files: "*.css",
			options: {
				parser: "css",
			},
		},
		{
			files: "*.md",
			options: {
				parser: "mdx",
			},
		},
	],
	plugins: [
		require.resolve("@trivago/prettier-plugin-sort-imports"),
		require.resolve("prettier-plugin-tailwindcss"),
	],
}
