require("dotenv").config()
const fs = require("fs")
const path = require("path")
const { getDatabaseConfig } = require("../lib/database.cjs")

const databaseType = getDatabaseConfig().type

console.log(`Database type detected: ${databaseType}`)

const src = path.resolve(__dirname, `../prisma/schema.${databaseType}.prisma`)
const dest = path.resolve(__dirname, "../prisma/schema.prisma")

fs.copyFileSync(src, dest)

console.log(`Copied ${src} to ${dest}`)
