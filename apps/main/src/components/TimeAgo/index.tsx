import useMounted from "~/hooks/useMounted"

interface TimeAgoProps {
	date: string
}

const UNITS: readonly { unit: Intl.RelativeTimeFormatUnit; seconds: number }[] =
	[
		{ unit: "year", seconds: 31_536_000 },
		{ unit: "month", seconds: 2_592_000 },
		{ unit: "week", seconds: 604_800 },
		{ unit: "day", seconds: 86_400 },
		{ unit: "hour", seconds: 3_600 },
		{ unit: "minute", seconds: 60 },
	]

const formatter = new Intl.RelativeTimeFormat("en", { numeric: "auto" })

const formatRelative = (timestamp: number) => {
	const diffSeconds = Math.round((timestamp - Date.now()) / 1000)
	const abs = Math.abs(diffSeconds)
	for (const { unit, seconds } of UNITS) {
		if (abs >= seconds) {
			return formatter.format(Math.trunc(diffSeconds / seconds), unit)
		}
	}
	return formatter.format(diffSeconds, "second")
}

export default function TimeAgo({ date }: TimeAgoProps) {
	const mounted = useMounted()
	const parsed = Date.parse(date)
	const iso = Number.isNaN(parsed) ? date : new Date(parsed).toISOString()
	const label =
		mounted && !Number.isNaN(parsed) ? formatRelative(parsed) : iso.slice(0, 10)

	return (
		<time dateTime={iso} title={iso.slice(0, 10)}>
			{label}
		</time>
	)
}
