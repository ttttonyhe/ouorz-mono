import type { NextApiRequest, NextApiResponse } from "next"
import { GITHUB_API } from "~/constants/apiURLs"

type ResDataType = {
	followers: number
	stars: number
}

const headers = {
	Authorization: process.env.GITHUB_TOKEN,
}

const github = async (
	_req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	const userResponse = await fetch(GITHUB_API.USER, {
		headers,
	})
	const userReposResponse = await fetch(`${GITHUB_API.REPOS}?per_page=100`, {
		headers,
	})

	const user = await userResponse.json()
	const repositories = await userReposResponse.json()

	const mine: any[] = Object.values(repositories).filter(
		(repo: { fork: any }) => !repo.fork
	)
	const stars = mine.reduce(
		(accumulator: any, repository: { [x: string]: any }) => {
			return accumulator + repository["stargazers_count"]
		},
		0
	)

	res.setHeader(
		"Cache-Control",
		"public, s-maxage=1200, stale-while-revalidate=600"
	)

	return res.status(200).json({
		followers: user.followers,
		stars,
	})
}

export default github
