require("dotenv").config({ quiet: true })
const path = require("node:path")
const pkg = require("./package.json")
const { getDatabaseConfig } = require("./lib/database.cjs")

getDatabaseConfig()

module.exports = {
	reactStrictMode: true,
	poweredByHeader: false,
	outputFileTracingRoot: path.resolve(__dirname, "../.."),
	env: {
		VERSION: pkg.version,
	},
	basePath: process.env.BASE_PATH,
	turbopack: {
		rules: {
			"*.svg": {
				loaders: ["@svgr/webpack"],
				as: "*.js",
			},
		},
	},
	async headers() {
		return [
			{
				source: `/(.*\\.js)`,
				headers: [
					{
						key: "Cache-Control",
						value: "public, max-age=2592000",
					},
				],
			},
		]
	},
}
