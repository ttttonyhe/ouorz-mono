import { Menu } from "@/components/Containers/Sidebar"
import { getPosts } from "@/database/getContent"
import responsive from "@/styles/responsive.module.css"
import { getPostRoute } from "@/utils/route"
import cn from "clsx"
import Link from "next/link"

const SidebarBlogPage = () => {
	const posts = getPosts()

	return (
		<div className="flex">
			<Menu />
			<section
				className={cn(
					responsive["panel-width"],
					"left-sidebar z-panel animate-panel-slide-in absolute flex h-full flex-col border-r bg-white-tinted dark:border-neutral-800 dark:bg-neutral-900"
				)}>
				<h1>Panel</h1>
				<div>
					{posts.map((post) => {
						const date = new Date(post.data.meta.date)
						return (
							<Link href={getPostRoute(post.slug)} key={post.path}>
								<div>
									<h2>{post.data.meta.title}</h2>
									<p>{date.toString()}</p>
									<p>{post.data.meta.description}</p>
								</div>
							</Link>
						)
					})}
				</div>
			</section>
		</div>
	)
}

export default SidebarBlogPage
