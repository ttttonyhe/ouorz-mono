import List from '~/components/List'
import getApi from '~/utilities/api'

export interface CategoryProps {
	params: {
		id: number
	}
}

/**
 * Get catgeory data from WP REST API
 *
 * @param {number} id
 * @return {*}
 */
export const getCateData = async (id: number): Promise<WPCate> => {
	const res = await fetch(
		getApi({
			cate: id.toString(),
			getCate: true,
		})
	)
	const data = await res.json()
	return data
}

const CategoryPage = async ({ params }: CategoryProps) => {
	const cate = await getCateData(params.id)

	return <List type="cate" cate={cate.id} />
}

export default CategoryPage
