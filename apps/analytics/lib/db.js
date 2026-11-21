import { PrismaClient } from "@prisma/client"
import { createPrismaAdapter } from "./prisma-adapter.cjs"

BigInt.prototype.toJSON = function () {
	const numeric = Number(this)
	return Number.isSafeInteger(numeric) ? numeric : this.toString()
}

const createClient = () =>
	new PrismaClient({
		adapter: createPrismaAdapter(),
	})

let prisma

if (process.env.NODE_ENV === "production") {
	prisma = createClient()
} else {
	if (!global.prisma) {
		global.prisma = createClient()
	}

	prisma = global.prisma
}

export default prisma
