require("dotenv").config()
const bcrypt = require("bcryptjs")
const prompts = require("prompts")
const { PrismaClient } = require("@prisma/client")
const { getDatabaseConfig } = require("../lib/database.cjs")

const prisma = new PrismaClient({ datasourceUrl: getDatabaseConfig().url })

async function main() {
	const [usernameArg, passwordArg, ...extra] = process.argv.slice(2)
	if (extra.length)
		throw new Error(
			"Usage: node scripts/change-password.js [username] [password]"
		)

	const questions = []
	if (!usernameArg)
		questions.push({
			type: "text",
			name: "username",
			message: "Account username",
		})
	if (!passwordArg)
		questions.push(
			{ type: "password", name: "password", message: "New password" },
			{
				type: "password",
				name: "confirmation",
				message: "Confirm new password",
			}
		)
	const answers = questions.length ? await prompts(questions) : {}
	const username = usernameArg || answers.username
	const password = passwordArg || answers.password
	if (!username || !password || Buffer.byteLength(password) > 72) {
		throw new Error(
			"Username and password are required; the password must not exceed 72 bytes"
		)
	}
	if (!passwordArg && password !== answers.confirmation)
		throw new Error("Passwords don't match")

	await prisma.account.update({
		where: { username },
		data: { password: bcrypt.hashSync(password, 10) },
	})
	console.log("Password changed for user", username)
}

main()
	.catch((error) => {
		console.error(error.code === "P2025" ? "Account not found" : error.message)
		process.exitCode = 1
	})
	.finally(() => prisma.$disconnect())
