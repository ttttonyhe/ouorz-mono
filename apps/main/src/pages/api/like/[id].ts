import type { NextApiRequest, NextApiResponse } from "next"

const like = (_req: NextApiRequest, res: NextApiResponse) => {
	res.status(200).json({ ok: true })
}

export default like
