import { Post } from "."
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

/**
 * Get all posts
 *
 * @param {string[]} [bySlugs=[]]
 * @return {Post[]}	{Post[]}
 */
export const getPosts = (bySlugs: string[] = []): Post[] => {
	const postPaths = _getFilePaths(CONTENT_ROOT_DIR)

	let posts: Post[] = []

	postPaths.forEach((postPath) => {
		const post = _getPost(postPath)

		// Validate
		const validatedPost = postSchema.safeParse(post)
		if (!validatedPost.success) {
			throw new Error(
				`occurred while parsing post /${postPath}.mdx\n${validatedPost.error.message}`
			)
		}

		// Filter by slugs
		let { data } = validatedPost.data
		const postSlug = data.slug.toString()
		if (bySlugs.length > 0 && !bySlugs.includes(postSlug)) {
			return
		}

		// Transform
		const { content } = validatedPost.data

		const postTransformers = [toLocalDate]
		postTransformers.forEach((transform) => {
			data = transform(data)
		})

		posts.push({
			slug: postSlug,
			path: postPath,
			data: {
				meta: data,
				source: content,
			},
		})
	})

	// Sort according to bySlug order
	if (bySlugs.length > 0) {
		posts = bySlugs.map((slug) => {
			const post = posts.find((post) => post.slug === slug)
			if (!post) {
				throw new Error(`Post with slug ${slug} not found`)
			}
			return post
		})
	}

	return posts
}

/**
 * Get all post slugs for SSG
 *
 * @return {string[]}	{string[]}
 */
export const getPostSlugs = (): string[] => {
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

		return validatedPost.data.data.slug.toString()
	})
}

/**
 * Get a post by its slug
 *
 * @param {string} slug
 * @return {Post}	{Post}
 */
export const getPostBySlug = (slug: string) => {
	const posts = getPosts([slug])

	if (!posts.length) {
		throw new Error(`Post with slug ${slug} not found`)
	}

	return posts[0]
}
