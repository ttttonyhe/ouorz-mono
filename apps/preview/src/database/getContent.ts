import fs from "node:fs"
import { resolve } from "node:path"

import { CONTENT_ROOT_DIR } from "@/constants/utils"
import { postSchema } from "@/database/schema"
import matter, { type GrayMatterFile } from "gray-matter"

import type { Post } from "."
import { toLocalDate } from "./transformers/post"

const getFilePaths = (dir: string): string[] => {
	const subDirs = fs.readdirSync(dir)
	const filePaths = subDirs.map((subDir) => {
		const res = resolve(dir, subDir)
		if (fs.statSync(res).isDirectory()) {
			return getFilePaths(res)
		}
		return res.slice(CONTENT_ROOT_DIR.length + 1)
	})

	return filePaths
		.flat()
		.filter((filePath) => filePath.endsWith(".mdx"))
		.map((filePath) => filePath.slice(0, -".mdx".length))
}

const getPostFile = (contentPath: string): GrayMatterFile<string> => {
	const contentSource = fs.readFileSync(
		`${CONTENT_ROOT_DIR}/${contentPath}.mdx`,
		"utf-8"
	)
	return matter(contentSource)
}

export const getPosts = (bySlugs: string[] = []): Post[] => {
	const postPaths = getFilePaths(CONTENT_ROOT_DIR)
	let posts: Post[] = []

	for (const postPath of postPaths) {
		const post = getPostFile(postPath)
		const validatedPost = postSchema.safeParse(post)
		if (!validatedPost.success) {
			throw new Error(
				`occurred while parsing post /${postPath}.mdx\n${validatedPost.error.message}`
			)
		}

		let { data } = validatedPost.data
		const postSlug = data.slug.toString()
		if (bySlugs.length > 0 && !bySlugs.includes(postSlug)) {
			continue
		}

		const { content } = validatedPost.data
		for (const transform of [toLocalDate]) {
			data = transform(data)
		}

		posts.push({
			slug: postSlug,
			path: postPath,
			data: {
				meta: data,
				source: content,
			},
		})
	}

	if (bySlugs.length > 0) {
		posts = bySlugs.map((slug) => {
			const post = posts.find((candidate) => candidate.slug === slug)
			if (!post) {
				throw new Error(`Post with slug ${slug} not found`)
			}
			return post
		})
	}

	return posts
}

export const getPostSlugs = (): string[] =>
	getFilePaths(CONTENT_ROOT_DIR).map((postPath) => {
		const post = getPostFile(postPath)
		const validatedPost = postSchema.safeParse(post)
		if (!validatedPost.success) {
			throw new Error(
				`occurred while parsing post /${postPath}.mdx\n${validatedPost.error.message}`
			)
		}
		return validatedPost.data.data.slug.toString()
	})

export const getPostBySlug = (slug: string): Post => {
	const [post] = getPosts([slug])
	if (!post) {
		throw new Error(`Post with slug ${slug} not found`)
	}
	return post
}
