const fs = require("fs-extra")
const path = require("path")
const { deleteSync } = require("del")
const prettier = require("prettier")

const src = path.resolve(__dirname, "../lang")
const dest = path.resolve(__dirname, "../build")
const files = fs.readdirSync(src).filter((file) => file.endsWith(".json"))

async function run() {
	await fs.ensureDir(dest)
	const removed = deleteSync([path.join(dest, "*.json")])
	for (const file of removed) console.log("Removed", file)

	for (const file of files) {
		const lang = require(`../lang/${file}`)
		const keys = Object.keys(lang).sort()

		const formatted = keys.reduce((obj, key) => {
			obj[key] = { defaultMessage: lang[key] }
			return obj
		}, {})

		const json = await prettier.format(JSON.stringify(formatted), {
			parser: "json",
		})

		fs.writeFileSync(path.resolve(dest, file), json)

		console.log(path.resolve(src, file), "->", path.resolve(dest, file))
	}
}

run().catch((error) => {
	console.error(error.message)
	process.exitCode = 1
})
