require("dotenv").config()
const fs = require("node:fs")
const path = require("node:path")
const { Readable } = require("node:stream")
const { pipeline } = require("node:stream/promises")
const tar = require("tar")
const maxmind = require("maxmind")

const REDIST_URL =
	"https://raw.githubusercontent.com/GitSquared/node-geolite2-redist/master/redist/GeoLite2-Country.tar.gz"
const DOWNLOAD_TIMEOUT = 60_000
const DATABASE_FILE = "GeoLite2-Country.mmdb"
const dest = path.resolve(__dirname, "../public/geo")

async function download() {
	await fs.promises.mkdir(dest, { recursive: true })
	const temporary = await fs.promises.mkdtemp(path.join(dest, ".download-"))
	try {
		const signal = AbortSignal.timeout(DOWNLOAD_TIMEOUT)
		const response = await fetch(REDIST_URL, { signal })
		if (!response.ok) {
			await response.body?.cancel()
			throw new Error("Geo database download returned HTTP " + response.status)
		}

		const archive = path.join(temporary, "country.tar.gz")
		await pipeline(
			Readable.fromWeb(response.body),
			fs.createWriteStream(archive),
			{ signal }
		)
		await tar.x({
			file: archive,
			cwd: temporary,
			strip: 1,
			strict: true,
			filter: (name, entry) =>
				entry.type === "File" && /^[^/]+\/GeoLite2-Country\.mmdb$/.test(name),
		})

		const database = path.join(temporary, DATABASE_FILE)
		await maxmind.open(database)
		await fs.promises.rename(database, path.join(dest, DATABASE_FILE))
		console.log("Saved geo database:", path.join(dest, DATABASE_FILE))
	} finally {
		await fs.promises.rm(temporary, { recursive: true, force: true })
	}
}

download().catch((error) => {
	console.error("Geo database build failed:", error.message)
	process.exitCode = 1
})
