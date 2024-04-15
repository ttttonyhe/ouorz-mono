import dynamic from "next/dynamic"

const PostRenderer = dynamic(() => import("./post"), {
	ssr: false,
	loading: () => <p>Loading...</p>,
})

export default PostRenderer
