module.exports = {
	extends: [
		"eslint:recommended",
		"plugin:react/recommended",
		"plugin:prettier/recommended",
	],
	ignorePatterns: ["node_modules", "dist"],
	plugins: ["react", "@typescript-eslint", "prettier"],
	env: {
		node: true,
		browser: true,
		es6: true,
	},
	rules: {
		camelcase: "error",
		"react/prop-types": "off",
		"react/display-name": "off",
		"react/react-in-jsx-scope": "off",
		"react/self-closing-comp": [
			"error",
			{
				component: true,
				html: true,
			},
		],
		"@typescript-eslint/explicit-function-return-type": "off",
		"@typescript-eslint/ban-ts-ignore": "off",
		"@typescript-eslint/ban-ts-comment": "off",
		"@typescript-eslint/no-explicit-any": "off",
		"@typescript-eslint/explicit-module-boundary-types": "off",
		"no-unused-vars": "off",
	},
	parser: "@typescript-eslint/parser",
	settings: {
		react: {
			pragma: "React",
			version: "detect",
		},
	},
};
