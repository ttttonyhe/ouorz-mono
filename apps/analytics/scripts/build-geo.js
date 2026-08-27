require("dotenv").config()
const fs = require("fs")
const path = require("path")
const https = require("https")
const zlib = require("zlib")
const tar = require("tar")

const REDIST_URL =
	"https://raw.githubusercontent.com/GitSquared/node-geolite2-redist/master/redist/GeoLite2-Country.tar.gz"

const maxmindUrl = (licenseKey) =>
	`https://download.maxmind.com/app/geoip_download` +
	`?edition_id=GeoLite2-Country&license_key=${licenseKey}&suffix=tar.gz`

const dest = path.resolve(__dirname, "../public/geo")

if (!fs.existsSync(dest)) {
	fs.mkdirSync(dest)
}

// MaxMind answers with a redirect to a signed URL, and answers an expired or
// malformed key with an error page rather than an archive. Following the one
// and rejecting the other keeps a bad key from reaching the gunzip stream,
// where it used to surface as `Z_BUF_ERROR: unexpected end of file`.
// `label` stands in for the URL in every message, because the MaxMind one
// carries the license key and these messages end up in build logs.
const request = (url, label, redirectsLeft = 5) =>
	new Promise((resolve, reject) => {
		https
			.get(url, (res) => {
				const { statusCode, headers } = res

				if (statusCode >= 300 && statusCode < 400 && headers.location) {
					res.resume()

					if (redirectsLeft === 0) {
						reject(new Error(`Too many redirects for ${label}`))
						return
					}

					resolve(
						request(
							new URL(headers.location, url).href,
							label,
							redirectsLeft - 1
						)
					)
					return
				}

				if (statusCode !== 200) {
					res.resume()
					reject(new Error(`${label} returned ${statusCode}`))
					return
				}

				resolve(res)
			})
			.on("error", reject)
	})

const extract = (res) =>
	new Promise((resolve, reject) => {
		const entries = res.pipe(zlib.createGunzip({})).pipe(tar.t())

		entries.on("entry", (entry) => {
			if (entry.path.endsWith(".mmdb")) {
				const filename = path.join(dest, path.basename(entry.path))
				entry.pipe(fs.createWriteStream(filename))

				console.log("Saved geo database:", filename)
			}
		})

		entries.on("error", reject)
		entries.on("finish", resolve)
	})

const download = async () => {
	const licenseKey = process.env.MAXMIND_LICENSE_KEY

	if (licenseKey) {
		try {
			return await extract(
				await request(maxmindUrl(licenseKey), "The MaxMind download")
			)
		} catch (error) {
			console.warn(
				`${error.message}. Falling back to the public redistribution.`
			)
		}
	}

	return extract(await request(REDIST_URL, "The public redistribution"))
}

download().catch((error) => {
	console.error(error)
	process.exit(1)
})
