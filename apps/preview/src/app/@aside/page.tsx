import Aside from "@/components/Containers/Aside"

const AsidePage = () => {
	return (
		<section className="flex h-full flex-col bg-white-tinted">
			<header className="sticky top-0 flex h-header w-full shrink-0 items-center border-b border-r dark:border-neutral-800 dark:bg-neutral-900">
				<h1>Aside</h1>
			</header>
			<Aside />
		</section>
	)
}

export default AsidePage
