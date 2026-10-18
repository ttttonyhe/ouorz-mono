const fs = require("fs")
const path = require("path")
const messages = require("../lang/en-US.json")

const dir = path.resolve(__dirname, "../lang")
const keys = Object.keys(messages).sort()
const filter = process.argv[2]

function run() {
	const files = fs
		.readdirSync(dir)
		.filter((file) => file.endsWith(".json"))
		.sort()
	if (filter && !files.includes(`${filter}.json`)) {
		throw new Error(`Unknown locale: ${filter}`)
	}

	for (const file of files) {
		if (filter && file !== `${filter}.json`) continue
		const lang = require(`../lang/${file}`)
		const id = file.replace(".json", "")
		let count = 0

		for (const key of keys) {
			const value = lang?.[key]
			if (typeof value !== "string" || !value.trim()) {
				console.error(`${id}: missing or invalid translation for ${key}`)
				count++
			}
		}

		if (count) {
			process.exitCode = 1
		} else {
			console.log(`${id}: complete (${keys.length} messages)`)
		}
	}
}

try {
	run()
} catch (error) {
	console.error(error.message)
	process.exitCode = 1
}
