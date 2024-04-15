import ArticleLayout from "@/components/Layouts/article"
import type { FC } from "react"

interface PostLayoutProps {
	article: React.ReactNode
	aside: React.ReactNode
}

const PostLayout: FC<PostLayoutProps> = ({ article, aside }) => {
	return (
		<div className="flex h-main overflow-hidden">
			{aside}
			<ArticleLayout>{article}</ArticleLayout>
		</div>
	)
}

export default PostLayout
