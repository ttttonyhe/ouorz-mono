import { BlogPageProps, getBlogPageData } from './page'

const BlogPageHead = async ({ params }: BlogPageProps) => {
	const page = await getBlogPageData(params.pgid)

	return (
		<>
			<title>{page.title.rendered} - TonyHe</title>
			<link
				rel="icon"
				href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>"
			/>
			<meta name="description" content={page.title.rendered} />
		</>
	)
}

export default BlogPageHead
