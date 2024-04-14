import { toLocalDate } from "./transformers/post"
import { CONTENT_ROOT_DIR } from "@/constants/utils"
import { postSchema } from "@/database/schema"
import fs from "fs"
import matter, { GrayMatterFile } from "gray-matter"
import { resolve } from "path"

/**
 * Recursively get all the filepaths in the content directory
 *
 * @param {string} dir
 * @return {*}  {string[]}
 */
const _getFilePaths = (dir: string): string[] => {
	const subDirs = fs.readdirSync(dir)
	const filePaths = subDirs.map((subDir) => {
		const res = resolve(dir, subDir)

		if (fs.statSync(res).isDirectory()) {
			return _getFilePaths(res)
		}

		return res.slice(CONTENT_ROOT_DIR.length + 1)
	})

	return filePaths
		.flat()
		.filter((filePath) => filePath.endsWith(".mdx"))
		.map((filePath) => filePath.split(".mdx")[0])
}

/**
 * Read the content and metadata of a post
 *
 * @param {string} contentPath
 * @return {*}  {GrayMatterFile<string>}
 */
const _getPost = (contentPath: string): GrayMatterFile<string> => {
	const contentSource = fs.readFileSync(
		`${CONTENT_ROOT_DIR}/${contentPath}.mdx`,
		"utf-8"
	)
	return matter(contentSource)
}

export const getPosts = () => {
	const postPaths = _getFilePaths(CONTENT_ROOT_DIR)
	return postPaths.map((postPath) => {
		const post = _getPost(postPath)

		// Validate
		const validatedPost = postSchema.safeParse(post)
		if (!validatedPost.success) {
			throw new Error(
				`occurred while parsing post /${postPath}.mdx\n${validatedPost.error.message}`
			)
		}

		// Transform
		let { data } = validatedPost.data
		const { content } = validatedPost.data

		const postTransformers = [toLocalDate]
		postTransformers.forEach((transform) => {
			data = transform(data)
		})

		return {
			slug: data.slug.toString(),
			path: postPath,
			data: {
				meta: data,
				source: content,
			},
		}
	})
}

export const getPostBySlug = (slug: string) => {
	const posts = getPosts()
	const post = posts.find((post) => post.slug === slug)

	if (!post) {
		throw new Error(`Post with slug ${slug} not found`)
	}

	return post
}
