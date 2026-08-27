const plugins = {
	"@tailwindcss/postcss": {},
}

if (process.env.NODE_ENV === "production") {
	plugins.cssnano = {}
}

module.exports = { plugins }
