function getDatabaseConfig(direct = false) {
	const names = direct
		? ["DATABASE_DIRECT_URL", "DATABASE_URL"]
		: ["DATABASE_POOLING_URL", "DATABASE_DIRECT_URL", "DATABASE_URL"]
	const name = names.find((key) => process.env[key])
	if (!name) {
		throw new Error(
			`Set ${names.join(" or ")} to a PostgreSQL or MySQL connection URL`
		)
	}
	const url = process.env[name]

	let protocol
	try {
		protocol = new URL(url).protocol
	} catch {
		throw new Error(`${name} is not a valid database URL`)
	}
	if (protocol === "prisma:" || protocol === "prisma+postgres:") {
		throw new Error(
			`${name} uses an unsupported Prisma proxy URL. Supply a PostgreSQL or MySQL connection URL.`
		)
	}
	const type = {
		"postgres:": "postgresql",
		"postgresql:": "postgresql",
		"mysql:": "mysql",
	}[protocol]
	if (!type)
		throw new Error(`${name} must use postgresql://, postgres://, or mysql://`)
	if (process.env.DATABASE_TYPE && process.env.DATABASE_TYPE !== type) {
		throw new Error(
			`DATABASE_TYPE does not match the ${type} connection in ${name}`
		)
	}
	return { url, type }
}

module.exports = { getDatabaseConfig }
