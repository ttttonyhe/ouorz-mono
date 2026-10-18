import { Button } from "@twilight-toolkit/ui"

export default function Top() {
	return (
		<div className="mt-4 flex gap-3">
			<div className="hidden grid-cols-10 gap-3 lg:grid">
				<div className="col-span-3">
					<Button
						target="_blank"
						href="mailto:lipeng.he@uwaterloo.ca"
						aria-label="Email"
						type="default"
						icon="mailFilled"
						className="w-full! text-3 leading-14 text-teal-600"
						rel="noreferrer">
						<span className="pl-1 tracking-normal">Email</span>
					</Button>
				</div>
				<div className="col-span-3">
					<Button
						target="_blank"
						href="https://scholar.google.com/citations?user=6yFlE_sAAAAJ"
						aria-label="Google Scholar"
						rel="noreferrer"
						type="default"
						icon="googleScholar"
						className="w-full! px-1! text-3 leading-14 text-blue-500">
						<span className="tracking-normal">Google Scholar</span>
					</Button>
				</div>
				<div className="col-span-2">
					<Button
						target="_blank"
						href="https://www.linkedin.com/in/~lhe"
						aria-label="LinkedIn"
						rel="noreferrer"
						type="default"
						icon="linkedIn"
						className="w-full! text-3 leading-14 text-blue-700">
						<span className="pl-1 tracking-normal">LinkedIn</span>
					</Button>
				</div>
				<div className="col-span-2">
					<Button
						target="_blank"
						href="https://github.com/ttttonyhe"
						aria-label="Github"
						rel="noreferrer"
						type="default"
						icon="github"
						className="w-full! text-3 leading-14 text-gray-800">
						<span className="pl-1 tracking-normal">Github</span>
					</Button>
				</div>
			</div>
			<div className="flex w-full gap-x-2 whitespace-nowrap lg:hidden">
				<div className="w-full">
					<Button
						target="_blank"
						href="mailto:lipeng.he@uwaterloo.ca"
						aria-label="Email"
						rel="noreferrer"
						type="default"
						icon="email"
						className="w-full! text-3 leading-14 text-gray-500 lg:hidden">
						<span className="tracking-normal">Email</span>
					</Button>
				</div>
				<div>
					<Button
						target="_blank"
						href="https://scholar.google.com/citations?user=6yFlE_sAAAAJ"
						aria-label="Google Scholar"
						rel="noreferrer"
						type="default"
						icon="googleScholar"
						className="h-full w-full! text-blue-500">
						<span className="pl-0.5 text-4 leading-14 tracking-normal lg:text-3">
							Google Scholar
						</span>
					</Button>
				</div>
			</div>
		</div>
	)
}
