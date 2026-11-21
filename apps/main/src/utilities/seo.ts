import { z } from "zod"
import type { LocalPage } from "~/content/pages"
import type { LocalPost } from "~/content/posts"
import {
	PERSON_ID,
	PROFILE_DESCRIPTION,
	PROFILE_TITLE,
	SITE_NAME,
	SITE_URL,
} from "~/content/profile"
import { isArticleContent } from "~/content/text"

export { PERSON_ID, PROFILE_DESCRIPTION, PROFILE_TITLE, SITE_NAME, SITE_URL }

export interface SeoPageProps {
	post?: LocalPost
	page?: LocalPage
	info?: { name: string; id: number; count: number; noindex?: boolean }
}

export interface BreadcrumbItem {
	name: string
	path: string
}

export interface PageMetadata {
	path: string
	title: string
	headline: string
	description: string
	language: "en" | "zh-CN"
	type: "ProfilePage" | "WebPage" | "CollectionPage" | "BlogPosting"
	noindex: boolean
	isErrorPage: boolean
	image: string
	datePublished?: string
	wordCount?: number
	articleSection?: string
	breadcrumbs?: BreadcrumbItem[]
}

interface StaticPageMeta {
	path: string
	label: string
	description: string
	type: PageMetadata["type"]
	noindex?: boolean
	isErrorPage?: boolean
	parent?: BreadcrumbItem
}

const PAGES_BREADCRUMB: BreadcrumbItem = { name: "Pages", path: "/pages" }

const STATIC_PAGES: readonly StaticPageMeta[] = [
	{
		path: "/",
		label: SITE_NAME,
		description: PROFILE_DESCRIPTION,
		type: "ProfilePage",
	},
	{
		path: "/pages",
		label: "Pages",
		description:
			"Explore Tony (Lipeng) He's software projects (Lune, Retroguard, ProChoice, Tailspace, Snapod, Autogrind), reading list, podcasts, links, and site directory.",
		type: "CollectionPage",
	},
	{
		path: "/links",
		label: "Friends and blogs",
		description:
			"Indie tools, friends' websites, and independent blogs collected by Tony (Lipeng) He, covering technology, research, and life in Chinese and English.",
		type: "CollectionPage",
		parent: PAGES_BREADCRUMB,
	},
	{
		path: "/podcasts",
		label: "Podcasts",
		description:
			"Podcasts Tony (Lipeng) He listens to, with links and notes for Chinese and English listeners.",
		type: "CollectionPage",
		parent: PAGES_BREADCRUMB,
	},
	{
		path: "/reading-list",
		label: "Reading list",
		description:
			"Books Tony (Lipeng) He is reading, has read, and plans to read, drawn from his Goodreads shelves.",
		type: "CollectionPage",
		parent: PAGES_BREADCRUMB,
	},
	{
		path: "/sponsor",
		label: "Support my work",
		description:
			"Support Tony (Lipeng) He's open-source projects and writing, with sponsorship options and acknowledgments of past supporters.",
		type: "WebPage",
	},
	{
		path: "/web3",
		label: "Web3 identities",
		description:
			"Tony (Lipeng) He's public blockchain identities, wallets, ENS, and Web3 profiles.",
		type: "WebPage",
		parent: PAGES_BREADCRUMB,
	},
	{
		path: "/dashboard",
		label: "Dashboard",
		description: "Tony (Lipeng) He's personal activity and website statistics.",
		type: "WebPage",
		noindex: true,
		parent: PAGES_BREADCRUMB,
	},
	{
		path: "/404",
		label: "Page not found",
		description:
			"This page could not be found. Visit Tony (Lipeng) He's academic profile or browse the site directory.",
		type: "WebPage",
		noindex: true,
		isErrorPage: true,
	},
	{
		path: "/_error",
		label: "Page unavailable",
		description:
			"This page is temporarily unavailable. Visit Tony (Lipeng) He's academic profile or try again later.",
		type: "WebPage",
		noindex: true,
		isErrorPage: true,
	},
]

const contentLanguageSchema = z.object({
	language: z.enum(["en", "zh-CN"]).optional(),
})

export const pageLanguageSchema = z.object({
	post: contentLanguageSchema.optional(),
	page: contentLanguageSchema.optional(),
})

const decodeEntity = (entity: string) => {
	switch (entity) {
		case "&amp;":
			return "&"
		case "&lt;":
			return "<"
		case "&gt;":
			return ">"
		case "&quot;":
			return '"'
		case "&#39;":
		case "&apos;":
			return "'"
		case "&nbsp;":
			return " "
		default:
			return " "
	}
}

const decodeHtmlToExcerpt = (html: string, maxLength = 160) => {
	const plain = html
		.replaceAll(/<[^>]*>/gu, " ")
		.replaceAll(/&(?:amp|lt|gt|quot|#39|apos|nbsp);/gu, (entity) =>
			decodeEntity(entity)
		)
		.replaceAll(/\s+/gu, " ")
		.trim()

	if (plain.length <= maxLength) return plain
	const slice = plain.slice(0, maxLength)
	const lastSpace = slice.lastIndexOf(" ")
	return lastSpace > maxLength * 0.6 ? slice.slice(0, lastSpace) : slice
}

export function getPageLanguage(props: z.infer<typeof pageLanguageSchema>) {
	return props.post?.language ?? props.page?.language ?? "en"
}

export function getPageMetadata(
	pathname: string,
	props: SeoPageProps
): PageMetadata {
	const language = getPageLanguage(props)
	const defaultImage = `${SITE_URL}/social-card.jpg`

	if (props.post) {
		const { post } = props
		const isArticle = isArticleContent(post.content.raw, post.post_metas.link)
		const category = post.post_categories[0]
		const breadcrumbs: BreadcrumbItem[] = [{ name: "Home", path: "/" }]
		if (category) {
			breadcrumbs.push({
				name: category.name,
				path: `/cate/${category.term_id}`,
			})
		}
		breadcrumbs.push({ name: post.title.rendered, path: `/post/${post.id}` })

		const metadata: PageMetadata = {
			path: `/post/${post.id}`,
			title: `${post.title.rendered} | ${SITE_NAME}`,
			headline: post.title.rendered,
			description: post.post_excerpt.four,
			language,
			type: isArticle ? "BlogPosting" : "WebPage",
			noindex: post.noindex || !isArticle,
			isErrorPage: false,
			image: post.post_img.url || defaultImage,
			breadcrumbs,
		}
		if (isArticle) {
			metadata.datePublished = post.date
			metadata.wordCount = post.post_metas.reading.word_count
		}
		if (category) {
			metadata.articleSection = category.name
		}
		return metadata
	}

	if (props.page) {
		const { page } = props
		return {
			path: `/page/${page.id}`,
			title: `${page.title.rendered} | ${SITE_NAME}`,
			headline: page.title.rendered,
			description: decodeHtmlToExcerpt(page.content.rendered),
			language,
			type: "WebPage",
			noindex: page.noindex,
			isErrorPage: false,
			image: defaultImage,
			breadcrumbs: [
				{ name: "Home", path: "/" },
				{ name: page.title.rendered, path: `/page/${page.id}` },
			],
		}
	}

	if (props.info) {
		return {
			path: `/cate/${props.info.id}`,
			title: `${props.info.name} | ${SITE_NAME}`,
			headline: props.info.name,
			description: `Browse ${props.info.name.toLowerCase()} collected by Tony (Lipeng) He.`,
			language,
			type: "CollectionPage",
			noindex: props.info.noindex ?? false,
			isErrorPage: false,
			image: defaultImage,
			breadcrumbs: [
				{ name: "Home", path: "/" },
				{ name: props.info.name, path: `/cate/${props.info.id}` },
			],
		}
	}

	const entry = STATIC_PAGES.find((item) => item.path === pathname)
	if (entry) {
		const isHome = entry.path === "/"
		const metadata: PageMetadata = {
			path: entry.path,
			title: isHome ? PROFILE_TITLE : `${entry.label} | ${SITE_NAME}`,
			headline: isHome ? SITE_NAME : entry.label,
			description: entry.description,
			language,
			type: entry.type,
			noindex: entry.noindex ?? false,
			isErrorPage: entry.isErrorPage ?? false,
			image: defaultImage,
		}
		if (!isHome && !entry.isErrorPage) {
			metadata.breadcrumbs = [
				{ name: "Home", path: "/" },
				...(entry.parent ? [entry.parent] : []),
				{ name: entry.label, path: entry.path },
			]
		}
		return metadata
	}

	return {
		path: pathname.startsWith("/") ? pathname : "/",
		title: SITE_NAME,
		headline: SITE_NAME,
		description: PROFILE_DESCRIPTION,
		language,
		type: "WebPage",
		noindex: true,
		isErrorPage: false,
		image: defaultImage,
	}
}
