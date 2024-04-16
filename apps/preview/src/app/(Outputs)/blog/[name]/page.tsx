import article from "@/styles/article.module.css"
import cn from "clsx"
import dynamic from "next/dynamic"

const getDynamicComponent = (name: string) =>
	dynamic(() => import(`@/content/${name}.mdx`), {
		ssr: false,
		loading: () => <div>Loading...</div>,
	})

const BlogPage = ({ params: { name } }: { params: { name: string } }) => {
	const Component = getDynamicComponent(name)
	return (
		<div>
			<div>Blog: {name}</div>
			<br />
			<div className={cn(article.renderer, "prose")}>
				<Component />
			</div>
		</div>
	)
}

export default BlogPage
