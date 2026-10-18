import { mkdir, readFile, writeFile } from "node:fs/promises"

import { Fragment, Interface } from "ethers"
import { z } from "zod"

const artifactSchema = z.object({
	abi: z.array(z.record(z.string(), z.unknown())).nonempty(),
})
const artifact = artifactSchema.parse(
	JSON.parse(
		await readFile(
			new URL(
				"../artifacts/contracts/TwilightBlog.sol/TwilightBlog.json",
				import.meta.url
			),
			"utf8"
		)
	)
)
const abi = new Interface(
	artifact.abi.map((fragment) => Fragment.from(fragment))
)
const output = new URL("../dist/", import.meta.url)
await mkdir(output, { recursive: true })
await writeFile(new URL("TwilightBlog.json", output), `${abi.formatJson()}\n`)
