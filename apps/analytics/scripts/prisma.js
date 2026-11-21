require("dotenv").config({ quiet: true })
const { spawn } = require("node:child_process")

const child = spawn(
	process.execPath,
	[require.resolve("prisma/build/index.js"), ...process.argv.slice(2)],
	{
		stdio: "inherit",
		env: process.env,
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
