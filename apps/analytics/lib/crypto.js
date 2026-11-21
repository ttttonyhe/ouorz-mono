import crypto from "node:crypto"
import { v4, v5, validate } from "uuid"
import bcrypt from "bcryptjs"
import { SignJWT, jwtVerify, CompactEncrypt, compactDecrypt } from "jose"
import { startOfMonth } from "date-fns"
import { getDatabaseConfig } from "./database.cjs"

const SALT_ROUNDS = 10
const CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
const TOKEN_LIFETIMES = {
	auth: "24 hours",
	collection: "30 minutes",
	share: "24 hours",
	realtime: "24 hours",
	"stats-service": "365 days",
}

const textEncoder = new TextEncoder()
const textDecoder = new TextDecoder()

function signingKey() {
	return new Uint8Array(crypto.createHash("sha256").update(secret()).digest())
}

export function hash(...args) {
	return crypto.createHash("sha512").update(args.join("")).digest("hex")
}

export function secret() {
	const value = process.env.HASH_SALT || getDatabaseConfig().url
	return hash(value)
}

export function salt() {
	return v5(hash(secret(), startOfMonth(new Date()).toUTCString()), v5.DNS)
}

export function uuid(...args) {
	if (!args.length) return v4()

	return v5(JSON.stringify(args), salt())
}

export function isValidUuid(s) {
	return validate(s)
}

export function getRandomChars(n) {
	let s = ""
	for (let i = 0; i < n; i++) {
		s += CHARS[crypto.randomInt(CHARS.length)]
	}
	return s
}

export function hashPassword(password) {
	return bcrypt.hashSync(password, SALT_ROUNDS)
}

export function checkPassword(password, hashed) {
	return bcrypt.compareSync(password, hashed)
}

export async function createToken(payload, purpose) {
	if (!Object.hasOwn(TOKEN_LIFETIMES, purpose)) {
		throw new Error("Invalid token purpose")
	}
	return new SignJWT(payload)
		.setProtectedHeader({ alg: "HS256" })
		.setIssuer("ouorz-analytics")
		.setAudience(purpose)
		.setIssuedAt()
		.setExpirationTime(TOKEN_LIFETIMES[purpose])
		.sign(signingKey())
}

export async function parseToken(token, purpose) {
	try {
		if (!Object.hasOwn(TOKEN_LIFETIMES, purpose)) return null
		const { payload } = await jwtVerify(token, signingKey(), {
			algorithms: ["HS256"],
			audience: purpose,
			issuer: "ouorz-analytics",
			maxTokenAge: TOKEN_LIFETIMES[purpose],
		})
		return Number.isFinite(payload.exp) ? payload : null
	} catch {
		return null
	}
}

export async function createSecureToken(payload) {
	const signed = await createToken(payload, "auth")
	return new CompactEncrypt(textEncoder.encode(signed))
		.setProtectedHeader({ alg: "dir", enc: "A256GCM" })
		.encrypt(signingKey())
}

export async function parseSecureToken(token) {
	try {
		const { plaintext } = await compactDecrypt(token, signingKey(), {
			keyManagementAlgorithms: ["dir"],
			contentEncryptionAlgorithms: ["A256GCM"],
		})

		return parseToken(textDecoder.decode(plaintext), "auth")
	} catch {
		return null
	}
}
