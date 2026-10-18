import { z } from "zod"

import { GITHUB_API } from "~/constants/apiURLs"
import { apiHandler, fetchJSON, PUBLIC_CACHE } from "~/lib/apiHandler"

const userSchema = z.object({
	followers: z.number().int().nonnegative(),
	public_repos: z.number().int().min(0).max(10_000),
})
const repositoriesSchema = z
	.array(
		z.object({
			fork: z.boolean(),
			stargazers_count: z.number().int().nonnegative(),
		})
	)
	.max(100)

export default apiHandler(["GET", "HEAD"], async (_req, res) => {
	const headers = new Headers({ Accept: "application/vnd.github+json" })
	if (process.env.GITHUB_TOKEN) {
		const token = process.env.GITHUB_TOKEN.replace(/^(?:Bearer|token)\s+/iu, "")
		headers.set("Authorization", `Bearer ${token}`)
	}

	const user = await fetchJSON(userSchema, GITHUB_API.USER, { headers })
	let stars = 0
	for (let page = 1; page <= Math.ceil(user.public_repos / 100); page++) {
		// oxlint-disable-next-line no-await-in-loop -- Avoid bursts against GitHub's shared rate limit.
		const repositories = await fetchJSON(
			repositoriesSchema,
			`${GITHUB_API.REPOS}?per_page=100&page=${page}`,
			{ headers }
		)
		stars += repositories.reduce(
			(sum, repo) => sum + (repo.fork ? 0 : repo.stargazers_count),
			0
		)
	}
	res.setHeader("Cache-Control", PUBLIC_CACHE)
	return res.status(200).json({ followers: user.followers, stars })
})
