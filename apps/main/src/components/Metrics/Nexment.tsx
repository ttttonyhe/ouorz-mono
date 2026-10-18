import format from "comma-number"
import useSWR from "swr"
import { z } from "zod"

import createFetcher from "~/lib/fetcher"

import MetricCard from "./Card"

const fetchComments = createFetcher(
	z.object({ count: z.number().int().nonnegative() })
)

export default function NexmentMetric() {
	const { data } = useSWR("/api/nexment", fetchComments)

	const count = data && format(data.count)
	const link =
		"https://supabase.com/dashboard/project/ainpzfmspbpvpfcatnwq/editor"

	return (
		<MetricCard
			icon="chat"
			value={count}
			description="Total Comments"
			link={link}
			colorHex="#10B981"
		/>
	)
}
