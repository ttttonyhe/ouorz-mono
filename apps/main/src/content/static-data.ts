import fs from "node:fs"
import path from "node:path"

interface Sponsor {
	name: string
	date: string
	unit: string
	amount: string | number
}

interface SponsorsData {
	donors: Sponsor[]
}

const DATA_DIR = path.join(process.cwd(), "content", "data")

export const getSponsors = (): SponsorsData => {
	const filePath = path.join(DATA_DIR, "sponsors.json")
	if (!fs.existsSync(filePath)) {
		return { donors: [] }
	}
	return JSON.parse(fs.readFileSync(filePath, "utf-8")) as SponsorsData
}
