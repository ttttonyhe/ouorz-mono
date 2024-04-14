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
		"@trivago/prettier-plugin-sort-imports",
		"prettier-plugin-tailwindcss",
	],
}
