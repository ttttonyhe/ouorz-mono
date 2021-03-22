import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
  followers: number
  stars: number
}

export default async (
  req: NextApiRequest,
  res: NextApiResponse<ResDataType>
) => {
  const userResponse = await fetch(
    'https://api.github.com/users/HelipengTony',
    {
      headers: {
        Authorization: process.env.GITHUB_TOKEN,
      },
    }
  )
  const userReposResponse = await fetch(
    'https://api.github.com/users/HelipengTony/repos?per_page=100',
    {
      headers: {
        Authorization: process.env.GITHUB_TOKEN,
      },
    }
  )

  const user = await userResponse.json()
  const repositories = await userReposResponse.json()

  const mine: any[] = Object.values(repositories).filter(
    (repo: { fork: any }) => !repo.fork
  )
  const stars = mine.reduce(
    (accumulator: any, repository: { [x: string]: any }) => {
      return accumulator + repository['stargazers_count']
    },
    0
  )

  res.setHeader(
    'Cache-Control',
    'public, s-maxage=1200, stale-while-revalidate=600'
  )

  return res.status(200).json({
    followers: user.followers,
    stars,
  })
}
