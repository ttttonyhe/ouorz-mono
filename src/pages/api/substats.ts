import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
  twitterFollowers: number
  sspaiFollowers: number
  zhihuFollowers: number
}

export default async (
  req: NextApiRequest,
  res: NextApiResponse<ResDataType>
) => {
  const response = await fetch(
    'https://api.spencerwoo.com/substats/?source=sspai&queryKey=tonyhe&source=twitter&queryKey=ttttonyhe&source=zhihu&queryKey=helipengtony'
  )

  const data = await response.json()

  res.setHeader(
    'Cache-Control',
    'public, s-maxage=1200, stale-while-revalidate=600'
  )

  return res.status(200).json({
    twitterFollowers: data.data.subsInEachSource.twitter,
    sspaiFollowers: data.data.subsInEachSource.sspai,
    zhihuFollowers: data.data.subsInEachSource.zhihu,
  })
}
