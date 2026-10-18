require("dotenv").config()
const { spawn } = require("node:child_process")
const { getDatabaseConfig } = require("../lib/database.cjs")

// Schema operations need an unpooled connection; client generation is offline.
const { url } = getDatabaseConfig(process.argv[2] !== "generate")
const child = spawn(
	process.execPath,
	[require.resolve("prisma/build/index.js"), ...process.argv.slice(2)],
	{
		stdio: "inherit",
		env: { ...process.env, DATABASE_URL: url },
	}
)

for (const signal of ["SIGINT", "SIGTERM"]) {
	process.on(signal, () => child.kill(signal))
}
child.on("error", (error) => {
	console.error(error.message)
	process.exitCode = 1
})
child.on("exit", (code) => {
	process.exitCode = code ?? 1
})
