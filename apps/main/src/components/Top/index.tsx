import { Button } from "@twilight-toolkit/ui"
import Link from "next/link"

export default function Top() {
	return (
		<div className="mt-4 grid lg:grid-cols-5 lg:gap-3">
			<div className="col-span-3 col-start-1 hidden grid-cols-3 gap-3 lg:grid">
				<a target="_blank" href="https://x.com/lipeng_he" rel="noreferrer">
					<Button
						type="default"
						icon="twitterX"
						className="!w-full text-3 leading-14 text-gray-500">
						Twitter
					</Button>
				</a>
				<a
					target="_blank"
					href="https://www.linkedin.com/in/~lhe"
					rel="noreferrer">
					<Button
						type="default"
						icon="linkedIn"
						className="!w-full text-3 leading-14 text-gray-500">
						<span className="tracking-normal">LinkedIn</span>
					</Button>
				</a>
				<a target="_blank" href="mailto:he@lipeng.ac" rel="noreferrer">
					<Button
						type="default"
						icon="email"
						className="!w-full text-3 leading-14 text-gray-500">
						<span className="tracking-normal">Email</span>
					</Button>
				</a>
			</div>
			<div className="lg:col-start-4 lg:col-end-6 lg:block flex whitespace-nowrap gap-x-2">
				<Button
					type="default"
					icon="email"
					className="!w-full lg:hidden text-3 leading-14 text-gray-500">
					<span className="tracking-normal">Email</span>
				</Button>
				<Link href="https://cal.com/tonyhe/15min" target="_blank">
					<Button
						type="default"
						icon="calendarSchedule"
						className="!w-full text-green-600">
						<span className="pl-0.5 text-4 leading-14 tracking-normal lg:text-3">
							Schedule a Meeting
						</span>
					</Button>
				</Link>
			</div>
		</div>
	)
}
