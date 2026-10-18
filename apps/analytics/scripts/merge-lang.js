const fs = require("fs")
const path = require("path")
const prettier = require("prettier")
const messages = require("../build/messages.json")

const dest = path.resolve(__dirname, "../lang")
const files = fs.readdirSync(dest).filter((file) => file.endsWith(".json"))
const keys = Object.keys(messages).sort()

async function run() {
	for (const file of files) {
		const lang = require(`../lang/${file}`)

		console.log(`Merging ${file}`)

		const merged = keys.reduce((obj, key) => {
			const message = lang[key]

			obj[key] = message || messages[key].defaultMessage

			if (!message) {
				console.log(`* Added key ${key}`)
			}

			return obj
		}, {})

		const json = await prettier.format(JSON.stringify(merged), {
			parser: "json",
		})

		fs.writeFileSync(path.resolve(dest, file), json)
	}
}

run().catch((error) => {
	console.error(error.message)
	process.exitCode = 1
})
