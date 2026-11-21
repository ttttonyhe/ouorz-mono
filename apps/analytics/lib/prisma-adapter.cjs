const { PrismaMariaDb } = require("@prisma/adapter-mariadb")
const { PrismaPg } = require("@prisma/adapter-pg")
const { getDatabaseConfig } = require("./database.cjs")

function createPrismaAdapter(direct = false) {
	const { url, type } = getDatabaseConfig(direct)

	if (type === "postgresql") {
		return new PrismaPg({ connectionString: url })
	}

	return new PrismaMariaDb(url)
}

module.exports = { createPrismaAdapter }
