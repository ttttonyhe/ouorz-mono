import nextVitals from "eslint-config-next/core-web-vitals"
import prettierConfig from "eslint-config-prettier"
import { defineConfig, globalIgnores } from "eslint/config"

export default defineConfig([
	...nextVitals,
	prettierConfig,
	{
		rules: {
			camelcase: "off",
			"@next/next/no-img-element": "off",
			"react-hooks/rules-of-hooks": "warn",
			"react-hooks/set-state-in-effect": "off",
			"react-hooks/preserve-manual-memoization": "off",
			"react-hooks/static-components": "off",
			"react-hooks/immutability": "off",
		},
	},
	globalIgnores([
		".next/**",
		"out/**",
		"build/**",
		"next-env.d.ts",
		"node_modules/**",
	]),
])
