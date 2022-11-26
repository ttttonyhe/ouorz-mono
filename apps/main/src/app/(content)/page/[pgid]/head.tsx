import { redirect } from 'next/navigation'
import { getPageData, PageProps } from './page'

const PageHead = async ({ params }: PageProps) => {
	const { pgid } = params
	const page = await getPageData(pgid)

	if (!page) {
		return redirect('/404')
	}

	const title = `${page.title.rendered} - TonyHe`

	return (
		<>
			<title>{title}</title>
			<link
				rel="icon"
				href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>"
			/>
			<meta name="description" content={page.title.rendered} />
		</>
	)
}

export default PageHead
