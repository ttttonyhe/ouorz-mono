interface Parameters {
	sticky?: boolean
	cate?: string
	cateExclude?: string
	perPage?: number
	getCate?: boolean
	mark?: number
	page?: number
	visit?: number
	post?: number
	search?: string
	sponsor?: boolean
	subs?: boolean
	count?: boolean
	postIDs?: boolean
}

export const getApi = ({
	sticky,
	cate,
	cateExclude,
	perPage,
	getCate,
	mark,
	page,
	post,
	visit,
	search,
	sponsor,
	subs,
	count,
	postIDs,
}: Parameters) => {
	if (getCate) {
		return `https://blog.ouorz.com/wp-json/wp/v2/categories/${cate}`
	}

	if (mark) {
		return `https://blog.ouorz.com/wp-json/tony/v1/mark/${mark}`
	}

	if (postIDs) {
		return 'https://blog.ouorz.com/wp-json/tony/v1/posts_ids'
	}

	if (page) {
		return `https://blog.ouorz.com/wp-json/wp/v2/pages/${page}`
	}

	if (post) {
		return `https://blog.ouorz.com/wp-json/wp/v2/posts/${post}`
	}

	if (visit) {
		return `https://blog.ouorz.com/wp-json/tony/v1/visit/${visit}`
	}

	if (sponsor) {
		return 'https://blog.ouorz.com/wp-content/themes/peg/com/data/donors.php'
	}

	if (subs) {
		return 'https://api.buttondown.email/v1/subscribers'
	}

	if (count) {
		return 'https://blog.ouorz.com/wp-json/tony/v1/poststats'
	}

	const s = sticky ? 'sticky=1' : sticky === undefined ? '' : 'sticky=0'
	const ce = cateExclude ? `&categories_exclude=${cateExclude}` : ''
	const c = cate ? `&categories=${cate}` : ''
	const p = perPage !== undefined ? `&per_page=${perPage}` : ''
	const sc = search ? `&search=${search}` : ''

	return `https://blog.ouorz.com/wp-json/wp/v2/posts?${s}${p}${c}${ce}${sc}`
}
