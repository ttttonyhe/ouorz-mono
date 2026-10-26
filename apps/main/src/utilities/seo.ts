import { z } from "zod"

import type { LocalPage } from "~/content/pages"
import type { LocalPost } from "~/content/posts"

export const SITE_URL = "https://lipeng.ac"
export const PERSON_ID = `${SITE_URL}/#person`
export const SITE_NAME = "Tony (Lipeng) He"
export const PROFILE_DESCRIPTION =
	"Tony (Lipeng) He, 贺莉朋, is a software engineer, startup founder, and CS PhD student researching Trustworthy AI at the University of Waterloo, Canada."

export interface SeoPageProps {
	post?: LocalPost
	page?: LocalPage
	info?: { name: string; id: number; count: number; noindex?: boolean }
}

export interface PageMetadata {
	path: string
	title: string
	description: string
	language: "en" | "zh-CN"
	type: "ProfilePage" | "WebPage" | "CollectionPage" | "BlogPosting"
	noindex: boolean
	image: string
	datePublished?: string
}

const staticPages = [
	[
		"/pages",
		"Pages",
		"Explore Tony He's research profile, writing, projects, reading list, and personal links.",
	],
	[
		"/links",
		"Friends and blogs",
		"Friends' websites and independent blogs collected by Tony He, with writing on technology, research, and life in Chinese and English.",
	],
	[
		"/podcasts",
		"Podcasts",
		"Podcasts Tony He listens to, with links and descriptions for Chinese and English listeners.",
	],
	[
		"/reading-list",
		"Reading list",
		"Books Tony He is reading, has read, and plans to read, drawn from his Goodreads shelves.",
	],
	[
		"/sponsor",
		"Support my work",
		"Support Tony He's writing and open-source projects. Find donation options and acknowledgments of past supporters.",
	],
	[
		"/web3",
		"Web3 identities",
		"Tony He's public blockchain identities, wallets, and Web3 profiles.",
	],
	[
		"/dashboard",
		"Dashboard",
		"Tony He's personal activity and website statistics.",
	],
	[
		"/404",
		"Page not found",
		"This page could not be found. Visit Tony He's academic profile or browse the site directory.",
	],
	[
		"/_error",
		"Page unavailable",
		"This page is temporarily unavailable. Visit Tony He's academic profile or try again later.",
	],
]

const contentLanguageSchema = z.object({
	language: z.enum(["en", "zh-CN"]).optional(),
})

export const pageLanguageSchema = z.object({
	post: contentLanguageSchema.optional(),
	page: contentLanguageSchema.optional(),
})

export function getPageLanguage(props: z.infer<typeof pageLanguageSchema>) {
	return props.post?.language ?? props.page?.language ?? "en"
}

export function getPageMetadata(
	pathname: string,
	props: SeoPageProps
): PageMetadata {
	const entry = staticPages.find(([path]) => path === pathname)
	const isCollection = [
		"/pages",
		"/links",
		"/podcasts",
		"/reading-list",
	].includes(pathname)
	const metadata: PageMetadata = {
		path: entry?.[0] ?? "/",
		title: entry ? `${entry[1]} | ${SITE_NAME}` : SITE_NAME,
		description: entry?.[2] ?? PROFILE_DESCRIPTION,
		language: getPageLanguage(props),
		type: "WebPage",
		noindex: ["/dashboard", "/404", "/_error"].includes(pathname),
		image: `${SITE_URL}/social-card.jpg`,
	}
	if (isCollection) metadata.type = "CollectionPage"
	if (pathname === "/") metadata.type = "ProfilePage"

	if (props.post) {
		const { post } = props
		metadata.path = `/post/${post.id}`
		metadata.title = `${post.title.rendered} | ${SITE_NAME}`
		metadata.description = post.post_excerpt.four
		const isArticle =
			!post.post_metas.link && post.content.raw.trim().length > 0
		metadata.type = isArticle ? "BlogPosting" : "WebPage"
		metadata.noindex = post.noindex || !isArticle
		if (isArticle) metadata.datePublished = post.date
		if (post.post_img.url) metadata.image = post.post_img.url
	} else if (props.page) {
		const { page } = props
		metadata.path = `/page/${page.id}`
		metadata.title = `${page.title.rendered} | ${SITE_NAME}`
		metadata.description = page.content.rendered
			.replaceAll(/<[^>]*>/gu, " ")
			.replaceAll(/\s+/gu, " ")
			.trim()
			.slice(0, 160)
		metadata.type = "WebPage"
		metadata.noindex = page.noindex
	} else if (props.info) {
		metadata.path = `/cate/${props.info.id}`
		metadata.title = `${props.info.name} | ${SITE_NAME}`
		metadata.description = `Browse ${props.info.name.toLowerCase()} by Tony He, with article summaries and links to the complete writing.`
		metadata.type = "CollectionPage"
		metadata.noindex = props.info.noindex ?? false
	}

	return metadata
}
