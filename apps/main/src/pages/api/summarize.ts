/* eslint-disable camelcase */
import Cors from 'cors'
import type { NextApiRequest, NextApiResponse } from 'next'
import html2plaintext from 'html2plaintext'
import { OPENAI_API } from '~/constants/apiURLs'
import { use } from '../../lib/middleware'

type ReqBodyType = {
	identifier: string
	content: string
}

type ResDataType =
	| {
			choices: [
				{
					text: string
				}
			]
	  }
	| {
			error: string
	  }

const removeCodeBlocks = (html: string) => {
	const regex = /<code\b[^>]*>[\s\S]*?<\/code>|<pre\b[^>]*>[\s\S]*?<\/pre>/gi
	return html.replace(regex, '')
}

const removeLinks = (string: string) => {
	const regex = /(https?:\/\/[^\s]+)/g
	return string.replace(regex, '[a link]')
}

const removeTrailingSpaces = (str: string) => {
	let string = str.replace(/[\s\uFEFF\xA0]+$/g, '').replace(/[^\S\r\n]+/g, ' ')
	return string.replace(/(\s*\n\s*){2,}/g, '\n ')
}

const summarize = async (
	req: NextApiRequest,
	res: NextApiResponse<ResDataType>
) => {
	await use(
		Cors({
			methods: ['POST'],
		}),
		req,
		res
	)

	let { identifier, content } = req.body as ReqBodyType

	if (content) {
		content = removeCodeBlocks(content)
		content = html2plaintext(content)
		content = removeLinks(content)
		content = removeTrailingSpaces(content)
		content = content.slice(0, 1925)

		const isAdmin =
			req.headers['token'] === process.env.REVALIDATION_REQUEST_TOKEN

		try {
			const response = await fetch(OPENAI_API.CACHING_PROXY, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: `Bearer ${process.env.OPENAI_API_KEY}`,
					Origin: req.headers.origin,
					...(isAdmin && {
						'Cache-Control': req.headers['cache-control'],
						Origin: 'https://www.ouorz.com',
					}),
				},
				body: JSON.stringify({
					targetUrl: OPENAI_API.COMPLETIONS,
					targetBody: {
						model: 'text-davinci-003',
						prompt: `${content}\n\nTl;dr`,
						temperature: 0.7,
						max_tokens: 500,
						top_p: 1.0,
						frequency_penalty: 0.0,
						presence_penalty: 1,
					},
					targetIdentifier: identifier,
				}),
			})

			const data = await response.json()

			res.setHeader('Cache-Control', `public, s-maxage=${3600 * 24 * 31}`)

			return res.status(200).json(data)
		} catch (error) {
			console.error(error)
			return res.status(500).json({ error: 'Internal server error' })
		}
	}

	return res.status(400).json({ error: 'No content provided' })
}

export const config = {
	api: {
		bodyParser: {
			sizeLimit: '2mb',
		},
	},
}

export default summarize
