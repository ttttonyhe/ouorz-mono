import { CategoryProps, getCateData } from './page'

const CategoryHead = async ({ params }: CategoryProps) => {
	const cate = await getCateData(params.id)
	const title = `${cate.name} - TonyHe`

	return (
		<>
			<title>{title}</title>
			<link
				rel="icon"
				href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗂️</text></svg>"
			/>
			<meta
				name="description"
				content={`TonyHe's content under category "${cate.name}"`}
			/>
		</>
	)
}

export default CategoryHead
