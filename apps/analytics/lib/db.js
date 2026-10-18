import { PrismaClient } from "@prisma/client"
import { getDatabaseConfig } from "./database.cjs"

BigInt.prototype.toJSON = function () {
	const int = Number.parseInt(this.toString())
	return int ?? this.toString()
}

const options = {
	datasourceUrl: getDatabaseConfig().url,
	log: [
		{
			emit: "event",
			level: "query",
		},
	],
}

let prisma

if (process.env.NODE_ENV === "production") {
	prisma = new PrismaClient(options)
} else {
	if (!global.prisma) {
		global.prisma = new PrismaClient(options)
	}

	prisma = global.prisma
}

export default prisma
