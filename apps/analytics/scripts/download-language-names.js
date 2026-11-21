const fs = require("node:fs")
const path = require("node:path")

const src = path.resolve(__dirname, "../lang")
const dest = path.resolve(__dirname, "../public/language")
const files = fs.readdirSync(src).filter((file) => file.endsWith(".json"))

const getUrl = (locale) =>
	`https://raw.githubusercontent.com/umpirsky/language-list/master/data/${locale}/language.json`

async function download() {
	fs.mkdirSync(dest, { recursive: true })

	for (const file of files) {
		const locale = file.replace("-", "_").replace(".json", "")
		const filename = path.join(dest, file)
		if (fs.existsSync(filename)) continue

		const response = await fetch(getUrl(locale), {
			signal: AbortSignal.timeout(30_000),
		})
		if (!response.ok) {
			throw new Error(`Failed to download ${locale}: HTTP ${response.status}`)
		}
		fs.writeFileSync(filename, await response.text())
		console.log("Downloaded ->", filename)
	}
}

download().catch((error) => {
	console.error(error.message)
	process.exitCode = 1
})
