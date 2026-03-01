/* Constants */
const LOCAL_CONTENT_API_ROOT = "/api/content"

/* API Methods */
// Like a single WP post
interface LikeAPIParams {
	id: number
}

const likePostAPI = (params: LikeAPIParams) => {
	const { id } = params
	return `/api/like/${id}`
}

// Get all WP post IDs
const postIDsAPI = () => {
	return `${LOCAL_CONTENT_API_ROOT}/allPostIDs`
}

// Subscribe to Listmonk newletter
const subscribeToListmonkAPI = () => {
	return "https://lists.lipeng.ac/api/public/subscription"
}

// Get a single WP page
interface PageAPIParams {
	id: number
}

const pageAPI = (params: PageAPIParams) => {
	const { id } = params
	return `${LOCAL_CONTENT_API_ROOT}/page?id=${id}`
}

// Get sponsors
const sponsorAPI = () => {
	return `${LOCAL_CONTENT_API_ROOT}/sponsors`
}

// Get post statistics
const postStatsAPI = () => {
	return `${LOCAL_CONTENT_API_ROOT}/postStats`
}

// Get a single WP post
interface PostAPIParams {
	id: number
}

const postAPI = (params: PostAPIParams) => {
	const { id } = params
	return `${LOCAL_CONTENT_API_ROOT}/post?id=${id}`
}

// Get a list of WP posts
interface PostsAPIParams {
	sticky?: boolean
	cate?: number
	cateExclude?: string
	perPage?: number
	search?: string
}

const postsAPI = (params: PostsAPIParams) => {
	const { sticky, cate, cateExclude, perPage, search } = params
	const s = sticky ? "sticky=1" : sticky === undefined ? "" : "sticky=0"
	const ce = cateExclude ? `&categories_exclude=${cateExclude}` : ""
	const c = cate ? `&categories=${cate}` : ""
	const p = perPage ? `&per_page=${perPage}` : ""
	const sc = search ? `&search=${search}` : ""
	return `${LOCAL_CONTENT_API_ROOT}/posts?${s}${p}${c}${ce}${sc}`
}

// Get WP category posts
interface CategoryAPIParams {
	id: number
	perPage?: number
}

const categoryAPI = (params: CategoryAPIParams) => {
	const { id } = params
	return `${LOCAL_CONTENT_API_ROOT}/category?id=${id}`
}

/* API Collection */
type API_TYPE = "internal" | "external"

type InternalAPIName = keyof (typeof API_COLLECTION)["internal"]
type InternalAPIMethod = (typeof API_COLLECTION)["internal"][InternalAPIName]

type ExternalAPIName = keyof (typeof API_COLLECTION)["external"]
type ExternalAPIMethod = (typeof API_COLLECTION)["external"][ExternalAPIName]

type API_METHOD = InternalAPIMethod | ExternalAPIMethod

const API_COLLECTION = {
	internal: {
		post: postAPI,
		category: categoryAPI,
		like: likePostAPI,
		allPostIDs: postIDsAPI,
		page: pageAPI,
		sponsors: sponsorAPI,
		postStats: postStatsAPI,
		posts: postsAPI,
	},
	external: {
		subscribeToListmonk: subscribeToListmonkAPI,
	},
}

/* GetAPI entry point */
const getAPI = <T extends API_TYPE>(
	type: T,
	name: keyof (typeof API_COLLECTION)[T],
	methodParams?: Parameters<API_METHOD>[0]
): ReturnType<API_METHOD> => {
	const apiMethod = API_COLLECTION[type as string][name as string]
	return apiMethod(methodParams)
}

export default getAPI
