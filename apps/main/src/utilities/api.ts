/* Constants */
const WP_API_ROOT = "https://blog.ouorz.com/wp-json/wp/v2"
const TONY_WP_API_ROOT = "https://blog.ouorz.com/wp-json/tony/v1"

/* API Methods */
// Like a single WP post
interface LikeAPIParams {
	id: number
}

const likePostAPI = (params: LikeAPIParams) => {
	const { id } = params
	return `${TONY_WP_API_ROOT}/mark/${id}`
}

// Get all WP post IDs
const postIDsAPI = () => {
	return `${TONY_WP_API_ROOT}/posts_ids`
}

// Subscribe to Buttondown newletter
const subscribeToButtondownAPI = () => {
	return "https://api.buttondown.email/v1/subscribers"
}

// Get search indices
const searchIndicesAPI = () => {
	return `${TONY_WP_API_ROOT}/searchIndexes`
}

// Get RSS data
const rssDataAPI = () => {
	return `${TONY_WP_API_ROOT}/rssData`
}

// Get a single WP page
interface PageAPIParams {
	id: number
}

const pageAPI = (params: PageAPIParams) => {
	const { id } = params
	return `${WP_API_ROOT}/pages/${id}`
}

// Post a post/page visit
const visitAPI = () => {
	return `${TONY_WP_API_ROOT}/visit`
}

// Get sponsors
const sponsorAPI = () => {
	return "https://blog.ouorz.com/wp-content/themes/peg/com/data/donors.php"
}

// Get post statistics
const postStatsAPI = () => {
	return `${TONY_WP_API_ROOT}/poststats`
}

// Get a single WP post
interface PostAPIParams {
	id: number
}

const postAPI = (params: PostAPIParams) => {
	const { id } = params
	return `${WP_API_ROOT}/posts/${id}`
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
	return `${WP_API_ROOT}/posts?${s}${p}${c}${ce}${sc}`
}

// Get WP category posts
interface CategoryAPIParams {
	id: number
	perPage?: number
}

const categoryAPI = (params: CategoryAPIParams) => {
	const { id } = params
	return `${WP_API_ROOT}/categories/${id}`
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
		searchIndices: searchIndicesAPI,
		rssData: rssDataAPI,
		page: pageAPI,
		visit: visitAPI,
		sponsors: sponsorAPI,
		postStats: postStatsAPI,
		posts: postsAPI,
	},
	external: {
		subscribeToButtondown: subscribeToButtondownAPI,
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
