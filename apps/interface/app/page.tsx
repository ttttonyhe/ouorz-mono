"use client"

import { useBlogSession } from "../hooks/use-blog-session"

const Page = () => {
	const blog = useBlogSession()

	return (
		<main>
			<h1>Hello {blog.account}</h1>
			<ul>
				<li>Blog uri: {blog.blogUri}</li>
				<li>Author address: {blog.authorAddress}</li>
				{blog.categories.map((category) => (
					<li key={category.id}>
						{category.name} : {category.description}
					</li>
				))}
			</ul>
			<button
				type="button"
				onClick={() => {
					void blog.connect()
				}}>
				Connect
			</button>
			<button
				type="button"
				onClick={() => {
					void blog.readUri()
				}}>
				Get blog uri
			</button>
			<button
				type="button"
				onClick={() => {
					void blog.readAuthor()
				}}>
				Get author address
			</button>
			<button
				type="button"
				onClick={() => {
					void blog.readCategories()
				}}>
				Get categories
			</button>
			<button
				type="button"
				onClick={() => {
					void blog.updateUri()
				}}>
				Update blog uri
			</button>
			<button
				type="button"
				onClick={() => {
					void blog.createCategory()
				}}>
				Create new category
			</button>
		</main>
	)
}

export default Page
