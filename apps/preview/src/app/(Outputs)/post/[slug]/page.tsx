import PostRenderer from "@/components/MDX/Renderers/post"
import { getPosts, getPostBySlug } from "@/database/getContent"
import { FC } from "react"

interface PostProps {
	params: {
		slug: string
	}
}

const Post: FC<PostProps> = ({ params: { slug } }) => {
	const {
		data: { source },
	} = getPostBySlug(slug)
	return <PostRenderer content={source} />
}

export const generateStaticParams = () => {
	const posts = getPosts()
	return posts.map((post) => ({
		slug: post.slug,
	}))
}

export default Post
