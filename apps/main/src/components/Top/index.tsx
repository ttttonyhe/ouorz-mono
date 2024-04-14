import { Button } from "@twilight-toolkit/ui"
import Link from "next/link"

export default function Top() {
	return (
		<div className="mt-4 grid lg:grid-cols-5 lg:gap-3">
			<div className="col-span-3 col-start-1 hidden grid-cols-3 gap-3 lg:grid">
				<a target="_blank" href="https://github.com/ttttonyhe" rel="noreferrer">
					<Button
						type="default"
						icon="github"
						className="!w-full text-3 leading-14 text-gray-700">
						<span className="tracking-normal">Github</span>
					</Button>
				</a>
				<a
					target="_blank"
					href="https://www.linkedin.com/in/~lhe"
					rel="noreferrer">
					<Button
						type="default"
						icon="linkedIn"
						className="!w-full text-3 leading-14 text-blue-600">
						<span className="tracking-normal">LinkedIn</span>
					</Button>
				</a>
				<a target="_blank" href="mailto:tony.hlp@hotmail.com" rel="noreferrer">
					<Button
						type="default"
						icon="email"
						className="!w-full text-3 leading-14 text-gray-500">
						<span className="tracking-normal">Email</span>
					</Button>
				</a>
			</div>
			<div className="lg:col-start-4 lg:col-end-6">
				<Link href="/post/126">
					<Button type="primary" icon="right" className="!w-full">
						<span className="text-4 leading-14 tracking-normal lg:text-3">
							More about me
						</span>
					</Button>
				</Link>
			</div>
		</div>
	)
}
