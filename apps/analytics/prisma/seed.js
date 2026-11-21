require("dotenv").config({ quiet: true })
const bcrypt = require("bcryptjs")
const { PrismaClient } = require("@prisma/client")
const { createPrismaAdapter } = require("../lib/prisma-adapter.cjs")

const prisma = new PrismaClient({ adapter: createPrismaAdapter() })
const SALT_ROUNDS = 10

const hashPassword = (password) => bcrypt.hashSync(password, SALT_ROUNDS)

async function main() {
	const password = process.env.ADMIN_PASSWORD
	if (!password || Buffer.byteLength(password) > 72) {
		throw new Error("ADMIN_PASSWORD is required and must not exceed 72 bytes")
	}
	await prisma.account.upsert({
		where: { username: "helipeng" },
		update: {},
		create: {
			username: "helipeng",
			password: hashPassword(password),
			is_admin: true,
		},
	})
}

main()
	.catch((e) => {
		console.error(e)
		process.exitCode = 1
	})
	.finally(async () => {
		await prisma.$disconnect()
	})
