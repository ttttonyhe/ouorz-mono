import type { NextApiRequest, NextApiResponse } from 'next'
import { v4 as uuidv4 } from 'uuid'
const CryptoJS = require('crypto-js')

type ResDataType = {
  profitability: number
  unpaidAmount: number
  temperature: number
  status: boolean
  load: number
}

const getAuthHeader = (
  apiKey,
  apiSecret,
  time,
  nonce,
  organizationId = '',
  request = {} as any
) => {
  const hmac = CryptoJS.algo.HMAC.create(CryptoJS.algo.SHA256, apiSecret)
  hmac.update(apiKey)
  hmac.update('\0')
  hmac.update(time)
  hmac.update('\0')
  hmac.update(nonce)
  hmac.update('\0')
  hmac.update('\0')
  if (organizationId) hmac.update(organizationId)
  hmac.update('\0')
  hmac.update('\0')
  hmac.update(request.method)
  hmac.update('\0')
  hmac.update(request.path)
  hmac.update('\0')

  return apiKey + ':' + hmac.finalize().toString(CryptoJS.enc.Hex)
}

export default async (
  req: NextApiRequest,
  res: NextApiResponse<ResDataType>
) => {
  const nonce = uuidv4().toString()

  const timeRes = await fetch('https://api2.nicehash.com/api/v2/time')
  const timeData = await timeRes.json()
  const localTimeDiff = timeData.serverTime - +new Date()
  const timeStamp = (
    timeData.serverTime || +new Date() + localTimeDiff
  ).toString()

  const response = await fetch(
    'https://api2.nicehash.com/main/api/v2/mining/rig2/' +
      process.env.NICEHASH_RIGID,
    {
      headers: {
        'X-Time': timeStamp,
        'X-Nonce': nonce,
        'X-Organization-Id': process.env.NICEHASH_ORGID,
        'X-Auth': getAuthHeader(
          process.env.NICEHASH_KEY,
          process.env.NICEHASH_SECRET,
          timeStamp,
          nonce,
          process.env.NICEHASH_ORGID,
          {
            method: 'GET',
            path: '/main/api/v2/mining/rig2/' + process.env.NICEHASH_RIGID,
          }
        ),
        'X-Request-Id': nonce,
        'X-User-Lang': 'en',
        'X-User-Agent': 'Node.js',
      } as any,
    }
  )

  const data = await response.json()

  res.setHeader(
    'Cache-Control',
    'public, s-maxage=1200, stale-while-revalidate=600'
  )

  return res.status(200).json({
    profitability: parseFloat(data.profitability) * Math.pow(10, 5),
    unpaidAmount: parseFloat(data.unpaidAmount) * Math.pow(10, 5),
    temperature: data.devices[1].temperature / Math.pow(10, 5),
    load: data.devices[1].load / Math.pow(10, 5),
    status: data.minerStatus === 'MINING',
  })
}
