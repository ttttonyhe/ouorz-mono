import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
  count: number
}

export default async (
  req: NextApiRequest,
  res: NextApiResponse<ResDataType>
) => {
  const response = await fetch(
    'https://ouorz-nexment.ouorz.com/1.1/classes/nexment_comments?count=1&limit=0',
    {
      headers: {
        'X-LC-Id': process.env.LC_ID,
        'X-LC-Key': process.env.LC_KEY,
      },
    }
  )

  const data = await response.json()

  res.setHeader(
    'Cache-Control',
    'public, s-maxage=1200, stale-while-revalidate=600'
  )

  return res.status(200).json({
    count: data.count,
  })
}
