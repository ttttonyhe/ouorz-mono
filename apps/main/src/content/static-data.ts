import fs from "node:fs"
import path from "node:path"

import { sponsorsSchema, type SponsorsData } from "~/content/schema"

const DATA_DIR = path.join(process.cwd(), "content", "data")

let cachedSponsors: SponsorsData | null = null

const loadSponsors = (): SponsorsData => {
	const filePath = path.join(DATA_DIR, "sponsors.json")
	if (!fs.existsSync(filePath)) {
		return { donors: [] }
	}
	return sponsorsSchema.parse(JSON.parse(fs.readFileSync(filePath, "utf-8")))
}

export const getSponsors = (): SponsorsData => {
	if (process.env.NODE_ENV !== "production") return loadSponsors()
	if (!cachedSponsors) cachedSponsors = loadSponsors()
	return cachedSponsors
}
