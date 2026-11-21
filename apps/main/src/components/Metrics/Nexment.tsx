import useSWR from "swr"
import { z } from "zod"
import createFetcher from "~/lib/fetcher"
import { formatNumber } from "~/utilities/format"

import MetricCard from "./Card"

const fetchComments = createFetcher(
	z.object({ count: z.number().int().nonnegative() })
)

export default function NexmentMetric() {
	const { data } = useSWR("/api/nexment", fetchComments)

	return (
		<MetricCard
			icon="chat"
			value={data && formatNumber(data.count)}
			description="Total Comments"
			link="https://supabase.com/dashboard/project/ainpzfmspbpvpfcatnwq/editor"
			colorHex="#10B981"
		/>
	)
}
