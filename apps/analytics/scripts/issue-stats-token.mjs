import "dotenv/config"
import prisma from "../lib/db.js"
import { createToken, hash } from "../lib/crypto.js"

try {
	const [username, id, ...extra] = process.argv.slice(2)
	const websiteId = Number(id)
	if (
		!username ||
		!Number.isSafeInteger(websiteId) ||
		websiteId <= 0 ||
		extra.length
	) {
		throw new Error(
			"Usage: bun run scripts/issue-stats-token.mjs <username> <website-id>"
		)
	}

	const [account, website] = await Promise.all([
		prisma.account.findUnique({ where: { username } }),
		prisma.website.findUnique({ where: { website_id: websiteId } }),
	])
	if (
		!account ||
		!website ||
		(!account.is_admin && website.user_id !== account.user_id)
	) {
		throw new Error("The account must own the website or be an administrator")
	}

	const token = await createToken(
		{
			user_id: account.user_id,
			website_id: websiteId,
			password_version: hash(account.password),
			account_version: account.updated_at?.toISOString() ?? null,
			is_admin: account.is_admin,
		},
		"stats-service"
	)
	process.stdout.write(`${token}\n`)
} catch (error) {
	console.error(error.message)
	process.exitCode = 1
} finally {
	await prisma.$disconnect()
}
