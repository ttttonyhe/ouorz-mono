import { Button } from '@twilight-toolkit/ui'
import Link from 'next/link'

export default function Top() {
	return (
		<div className="mt-4 grid lg:grid-cols-5 lg:gap-3">
			<div className="grid-cols-3 gap-3 col-start-1 col-span-3 hidden lg:grid">
				<a
					target="_blank"
					href="https://github.com/HelipengTony"
					rel="noreferrer"
				>
					<Button
						type="default"
						icon="github"
						className="w-full text-gray-700 text-3 leading-14"
					>
						<span className="tracking-normal">Github</span>
					</Button>
				</a>
				<a
					target="_blank"
					href="https://twitter.com/ttttonyhe"
					rel="noreferrer"
				>
					<Button
						type="default"
						icon="twitter"
						className="w-full text-blue-400 text-3 leading-14"
					>
						<span className="tracking-normal">Twitter</span>
					</Button>
				</a>
				<a target="_blank" href="mailto:tony.hlp@hotmail.com" rel="noreferrer">
					<Button
						type="default"
						icon="email"
						className="w-full text-gray-500 text-3 leading-14"
					>
						<span className="tracking-normal">Email</span>
					</Button>
				</a>
			</div>
			<div className="lg:col-start-4 lg:col-end-6">
				<Link href="/post/126">
					<a>
						<Button type="primary" icon="right" className="w-full">
							<span className="tracking-normal text-4 leading-14 lg:text-3">
								More about me
							</span>
						</Button>
					</a>
				</Link>
			</div>
		</div>
	)
}
