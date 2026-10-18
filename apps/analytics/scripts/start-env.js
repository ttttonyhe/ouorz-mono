const { spawn } = require("node:child_process")

const child = spawn(
	process.execPath,
	[
		require.resolve("next/dist/bin/next"),
		"start",
		"--port",
		process.env.PORT || "3000",
		"--hostname",
		process.env.HOSTNAME || "0.0.0.0",
	],
	{ stdio: "inherit" }
)

for (const signal of ["SIGINT", "SIGTERM"]) {
	process.on(signal, () => child.kill(signal))
}
child.on("error", (error) => {
	console.error(error)
	process.exitCode = 1
})
child.on("exit", (code, signal) => {
	process.exitCode = code ?? (signal === "SIGINT" ? 130 : 143)
})
