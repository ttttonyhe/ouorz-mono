const LOCAL_CONTENT_API_ROOT = "/api/content"

interface IdParameter {
	id: number
}

interface PostsAPIParams {
	sticky?: boolean
	cate?: number
	cateExclude?: string
	perPage?: number
	search?: string
}

const postsAPI = (params: PostsAPIParams = {}) => {
	const { sticky, cate, cateExclude, perPage, search } = params
	const query = new URLSearchParams()
	if (sticky !== undefined) query.set("sticky", sticky ? "1" : "0")
	if (cate !== undefined) query.set("categories", String(cate))
	if (cateExclude !== undefined) query.set("categories_exclude", cateExclude)
	if (perPage !== undefined) query.set("per_page", String(perPage))
	if (search !== undefined) query.set("search", search)
	return `${LOCAL_CONTENT_API_ROOT}/posts?${query}`
}

const api = {
	internal: {
		post: ({ id }: IdParameter) => `${LOCAL_CONTENT_API_ROOT}/post?id=${id}`,
		category: ({ id }: IdParameter) =>
			`${LOCAL_CONTENT_API_ROOT}/category?id=${id}`,
		like: ({ id }: IdParameter) => `/api/like/${id}`,
		allPostIDs: () => `${LOCAL_CONTENT_API_ROOT}/allPostIDs`,
		page: ({ id }: IdParameter) => `${LOCAL_CONTENT_API_ROOT}/page?id=${id}`,
		sponsors: () => `${LOCAL_CONTENT_API_ROOT}/sponsors`,
		postStats: () => `${LOCAL_CONTENT_API_ROOT}/postStats`,
		posts: postsAPI,
	},
	external: {
		subscribeToListmonk: () =>
			"https://lists.lipeng.ac/api/public/subscription",
	},
}

export default api
