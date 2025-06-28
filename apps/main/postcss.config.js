module.exports = {
	plugins: {
		"@tailwindcss/postcss": {},
		"@tailwindcss/nesting": {},
		...(process.env.NODE_ENV === "production" ? { cssnano: {} } : {}),
	},
}
