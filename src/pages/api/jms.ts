import type { NextApiRequest, NextApiResponse } from 'next'

type ResDataType = {
  total: number
  used: number
}

export default async (
  req: NextApiRequest,
  res: NextApiResponse<ResDataType>
) => {
  const response = await fetch(
    'https://justmysocks3.net/members/getbwcounter.php?service=106056&id=4d5795d7-60a5-4880-8a72-240031508dd6'
  )

  const data = await response.json()

  res.setHeader(
    'Cache-Control',
    'public, s-maxage=1200, stale-while-revalidate=600'
  )

  return res.status(200).json({
    total: data.monthly_bw_limit_b / Math.pow(10, 9),
    used: data.bw_counter_b / Math.pow(10, 9),
  })
}
