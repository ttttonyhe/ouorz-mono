// import { PrismaClient } from "@prisma/client"
import { PrismaClient } from "@prisma/client/edge"
import { withAccelerate } from '@prisma/extension-accelerate'

BigInt.prototype.toJSON = function () {
	const int = Number.parseInt(this.toString())
	return int ?? this.toString()
}

const options = {
	log: [
		{
			emit: "event",
			level: "query",
		},
	],
}

let prisma

if (process.env.NODE_ENV === "production") {
	// prisma = new PrismaClient(options)
	prisma = new PrismaClient(options).$extends(withAccelerate())
} else {
	if (!global.prisma) {
		// prisma = new PrismaClient(options)
		global.prisma = new PrismaClient(options).$extends(withAccelerate())
	}

	prisma = global.prisma
}

export default prisma
