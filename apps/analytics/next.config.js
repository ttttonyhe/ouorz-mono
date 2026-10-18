require("dotenv").config()
const pkg = require("./package.json")
const path = require("node:path")
const { getDatabaseConfig } = require("./lib/database.cjs")

getDatabaseConfig()

module.exports = {
	outputFileTracingRoot: path.resolve(__dirname, "../.."),
	env: {
		VERSION: pkg.version,
	},
	basePath: process.env.BASE_PATH,
	eslint: {
		ignoreDuringBuilds: true,
	},
	webpack(config) {
		config.module.rules.push({
			test: /\.svg$/,
			issuer: /\.js$/,
			use: ["@svgr/webpack"],
		})

		return config
	},
	async headers() {
		return [
			{
				source: `/(.*\\.js)`,
				headers: [
					{
						key: "Cache-Control",
						value: "public, max-age=2592000", // 30 days
					},
				],
			},
		]
	},
}
