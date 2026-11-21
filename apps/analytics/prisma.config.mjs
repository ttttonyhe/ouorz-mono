import { config as loadEnv } from "dotenv"
import { defineConfig } from "prisma/config"
import database from "./lib/database.cjs"

loadEnv({ quiet: true })

const direct =
	process.argv.includes("db") || process.argv.includes("migrate")
const { url } = database.getDatabaseConfig(direct)

export default defineConfig({
	schema: "prisma/schema.prisma",
	datasource: {
		url,
	},
})
