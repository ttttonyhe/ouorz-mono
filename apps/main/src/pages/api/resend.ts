import { timingSafeEqual } from "node:crypto"

import type { NextApiRequest, NextApiResponse } from "next"
import { z } from "zod"

import { apiHandler, fetchJSON, requireEnv } from "~/lib/apiHandler"
import {
	mailerBodySchema,
	type MailerRequestBody,
	parseBody,
} from "~/lib/requestBody"

const FROM_ADDRESS =
	process.env.RESEND_FROM_ADDRESS || "Nexment <no-reply@nexment.ouorz.com>"

const escapeHtml = (value: string) =>
	value
		.replaceAll("&", "&amp;")
		.replaceAll("<", "&lt;")
		.replaceAll(">", "&gt;")
		.replaceAll('"', "&quot;")
		.replaceAll("'", "&#39;")

function buildEmailHtml(body: MailerRequestBody): string {
	return `
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 0">
<tr><td align="center">
<table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;max-width:480px;width:100%">
	<tr><td style="background-color:#1e293b;padding:24px 32px">
		<span style="color:#ffffff;font-size:16px;font-weight:600;letter-spacing:-0.01em">Nexment</span>
	</td></tr>
	<tr><td style="padding:32px">
		<p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#334155">
			<strong style="color:#1e293b">${escapeHtml(body.fromName)}</strong> left a reply:
		</p>
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
		<tr><td style="border-left:3px solid #1e293b;padding:12px 16px;background-color:#f8fafc;border-radius:0 6px 6px 0">
			<p style="margin:0;font-size:14px;line-height:1.6;color:#475569">${escapeHtml(body.content)}</p>
		</td></tr>
		</table>
		<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:28px">
		<tr><td style="background-color:#1e293b;border-radius:6px;padding:10px 20px">
			<a href="${escapeHtml(body.url)}" style="color:#ffffff;font-size:14px;font-weight:500;text-decoration:none;display:inline-block">Reply back</a>
		</td></tr>
		</table>
	</td></tr>
	<tr><td style="padding:20px 32px;border-top:1px solid #e2e8f0">
		<p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.5">You're receiving this because someone replied to your comment.</p>
	</td></tr>
</table>
</td></tr>
</table>
</body>
</html>`
}

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
	const expected = Buffer.from(`Bearer ${requireEnv("RESEND_REQUEST_TOKEN")}`)
	const received = Buffer.from(req.headers.authorization ?? "")
	if (
		received.length !== expected.length ||
		!timingSafeEqual(received, expected)
	) {
		return res.status(401).json({ error: "Unauthorized" })
	}

	const body = parseBody(mailerBodySchema, req)

	if (!body) {
		res.status(400).json({ error: "Invalid request body" })
		return
	}

	const data = await fetchJSON(
		z.object({ id: z.string().min(1) }),
		"https://api.resend.com/emails",
		{
			method: "POST",
			headers: {
				"Content-Type": "application/json",
				Authorization: `Bearer ${requireEnv("RESEND_API_KEY")}`,
			},
			body: JSON.stringify({
				from: FROM_ADDRESS,
				to: [body.toEmail],
				subject: `You received a new reply from ${body.fromName}!`,
				html: buildEmailHtml(body),
			}),
		}
	)

	res.status(200).json(data)
}

export const config = { api: { bodyParser: { sizeLimit: "32kb" } } }

export default apiHandler(["POST"], handler)
