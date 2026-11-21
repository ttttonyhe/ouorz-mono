import Head from "next/head"
import { useEffect } from "react"
import {
	ALL_PUBLICATIONS,
	AWARDS,
	PERSON_ID,
	PERSON_IDENTITY,
	PREPRINTS,
	PROFILE_DESCRIPTION,
	PROFILE_UPDATED_AT,
	PROJECTS,
	RESEARCH_TOPICS,
	SITE_NAME,
	SITE_URL,
	WEBSITE_ID,
} from "~/content/profile"
import type { PageMetadata } from "~/utilities/seo"

interface Props {
	metadata: PageMetadata
}

const buildPersonNode = () => ({
	"@type": "Person",
	"@id": PERSON_ID,
	name: PERSON_IDENTITY.name,
	givenName: PERSON_IDENTITY.givenName,
	familyName: PERSON_IDENTITY.familyName,
	additionalName: PERSON_IDENTITY.additionalName,
	alternateName: [...PERSON_IDENTITY.alternateNames],
	identifier: {
		"@type": "PropertyValue",
		propertyID: "ORCID",
		value: PERSON_IDENTITY.orcid,
		url: PERSON_IDENTITY.orcidUrl,
	},
	url: `${SITE_URL}/`,
	mainEntityOfPage: `${SITE_URL}/`,
	email: `mailto:${PERSON_IDENTITY.email}`,
	image: {
		"@type": "ImageObject",
		url: `${SITE_URL}/profile.jpg`,
		width: 708,
		height: 708,
		caption:
			"Tony (Lipeng) He teaching an undergraduate computer science class",
	},
	description: PROFILE_DESCRIPTION,
	jobTitle: PERSON_IDENTITY.jobTitle,
	affiliation: {
		"@type": "CollegeOrUniversity",
		name: "University of Waterloo",
		url: "https://uwaterloo.ca/",
		sameAs: [
			"https://ror.org/01aff2v68",
			"https://www.wikidata.org/wiki/Q1049470",
		],
		department: {
			"@type": "Organization",
			name: "David R. Cheriton School of Computer Science",
			url: "https://cs.uwaterloo.ca/",
			sameAs: "https://www.wikidata.org/wiki/Q5238801",
		},
		address: {
			"@type": "PostalAddress",
			streetAddress: "200 University Ave W",
			addressLocality: "Waterloo",
			addressRegion: "ON",
			postalCode: "N2L 3G1",
			addressCountry: "CA",
		},
	},
	memberOf: [
		{
			"@type": "ResearchOrganization",
			name: "Secure Systems Group (SSG)",
			url: "https://ssg-research.github.io",
		},
		{
			"@type": "ResearchOrganization",
			name: "Cryptography, Security, and Privacy (CrySP) Lab",
			url: "https://crysp.uwaterloo.ca",
		},
		{
			"@type": "ResearchOrganization",
			name: "Cybersecurity and Privacy Institute (CPI)",
			url: "https://uwaterloo.ca/cybersecurity-privacy-institute",
		},
	],
	worksFor: {
		"@type": "Organization",
		name: "Retrograde Labs",
		url: "https://inretrograde.io",
		sameAs: [
			"https://www.linkedin.com/company/retrograde-labs",
			"https://github.com/RetrogradeLabs",
		],
		address: {
			"@type": "PostalAddress",
			addressLocality: "Waterloo",
			addressRegion: "ON",
			addressCountry: "CA",
		},
		contactPoint: {
			"@type": "ContactPoint",
			contactType: "research and engineering",
			email: PERSON_IDENTITY.email,
			url: "https://inretrograde.io",
		},
	},
	workLocation: {
		"@type": "Place",
		name: "William G. Davis Computer Research Centre, DC 3333",
		address: {
			"@type": "PostalAddress",
			streetAddress: "200 University Ave W, DC 3333",
			addressLocality: "Waterloo",
			addressRegion: "ON",
			postalCode: "N2L 3G1",
			addressCountry: "CA",
		},
	},
	alumniOf: [
		{
			"@type": "CollegeOrUniversity",
			name: "University of Waterloo",
			url: "https://uwaterloo.ca/",
			sameAs: [
				"https://ror.org/01aff2v68",
				"https://www.wikidata.org/wiki/Q1049470",
			],
		},
		{
			"@type": "CollegeOrUniversity",
			name: "Nanyang Technological University",
			url: "https://www.ntu.edu.sg/",
			sameAs: "https://www.wikidata.org/wiki/Q721064",
		},
	],
	hasCredential: {
		"@type": "EducationalOccupationalCredential",
		name: "Honours Bachelor of Mathematics (Co-op), Minor in Computing",
		credentialCategory: "degree",
		recognizedBy: {
			"@type": "CollegeOrUniversity",
			name: "University of Waterloo",
			url: "https://uwaterloo.ca/",
		},
	},
	knowsAbout: RESEARCH_TOPICS.map((topic) => ({
		"@type": "Thing",
		name: topic.name,
		sameAs: topic.wikidata,
	})),
	award: [...AWARDS],
	sameAs: [...PERSON_IDENTITY.sameAs],
})

const parseAuthorNodes = (authors: string) =>
	authors
		.split(/,\s+and\s+|,\s+|\s+and\s+/u)
		.map((entry) => entry.replaceAll("*", "").trim())
		.filter((name) => name.length > 0 && name.toLowerCase() !== "et al.")
		.map((name) =>
			name === PERSON_IDENTITY.name
				? { "@id": PERSON_ID }
				: { "@type": "Person", name }
		)

const buildScholarlyArticleNodes = () =>
	[...ALL_PUBLICATIONS, ...PREPRINTS]
		.filter((paper) => Boolean(paper.canonicalUrl ?? paper.links[0]?.href))
		.map((paper) => {
			const url = paper.canonicalUrl ?? paper.links[0]?.href
			const sameAsLinks = [
				paper.doi ? `https://doi.org/${paper.doi}` : undefined,
				paper.links[0]?.href !== url ? paper.links[0]?.href : undefined,
			].filter((value): value is string => Boolean(value))

			return {
				"@type": "ScholarlyArticle",
				name: paper.title,
				headline: paper.title,
				url,
				sameAs: sameAsLinks.length > 0 ? sameAsLinks : undefined,
				author: parseAuthorNodes(paper.authors),
				datePublished: paper.datePublished,
				publication: paper.venue
					? {
							"@type": "PublicationEvent",
							name: paper.venue.name,
							url: paper.venue.href,
						}
					: undefined,
			}
		})

const buildSoftwareNodes = () =>
	PROJECTS.map((project) => ({
		"@type": "SoftwareApplication",
		name: project.title,
		description: `${project.description}. ${project.summary}`,
		url: project.href,
		applicationCategory: project.applicationCategory,
		operatingSystem: project.operatingSystem,
		sameAs: project.repo,
		author: { "@id": PERSON_ID },
	}))

const buildJsonLdGraph = (metadata: PageMetadata, url: string) => {
	const isProfile = metadata.type === "ProfilePage"
	const isArticle = metadata.type === "BlogPosting"
	const isPages = metadata.path === "/pages"
	const person = buildPersonNode()
	const website = {
		"@type": "WebSite",
		"@id": WEBSITE_ID,
		url: `${SITE_URL}/`,
		name: SITE_NAME,
		alternateName: PERSON_IDENTITY.chineseName,
		inLanguage: ["en", "zh-CN"],
		publisher: { "@id": PERSON_ID },
	}
	const page = {
		"@type": metadata.type,
		"@id": `${url}${isArticle ? "#article" : "#webpage"}`,
		url,
		name: metadata.title,
		headline: metadata.headline,
		description: metadata.description,
		inLanguage: metadata.language,
		isPartOf: { "@id": WEBSITE_ID },
		author: { "@id": PERSON_ID },
		publisher: isArticle ? { "@id": PERSON_ID } : undefined,
		mainEntityOfPage: isArticle
			? { "@type": "WebPage", "@id": url }
			: undefined,
		mainEntity: isProfile ? { "@id": PERSON_ID } : undefined,
		datePublished: metadata.datePublished,
		dateModified: isProfile ? PROFILE_UPDATED_AT : undefined,
		wordCount: metadata.wordCount,
		articleSection: metadata.articleSection,
		image: metadata.image,
	}

	const breadcrumbNode =
		metadata.breadcrumbs && metadata.breadcrumbs.length > 0
			? {
					"@type": "BreadcrumbList",
					"@id": `${url}#breadcrumb`,
					itemListElement: metadata.breadcrumbs.map((crumb, index) => ({
						"@type": "ListItem",
						position: index + 1,
						name: crumb.name,
						item: `${SITE_URL}${crumb.path}`,
					})),
				}
			: undefined

	const graphNodes: object[] = [person, website, page]
	if (breadcrumbNode) graphNodes.push(breadcrumbNode)
	if (isProfile) graphNodes.push(...buildScholarlyArticleNodes())
	if (isPages) graphNodes.push(...buildSoftwareNodes())

	return {
		"@context": "https://schema.org",
		"@graph": graphNodes,
	}
}

export default function SiteSeo({ metadata }: Props) {
	const {
		title,
		description,
		language,
		type,
		image,
		datePublished,
		noindex,
		isErrorPage,
	} = metadata
	const url = `${SITE_URL}${metadata.path}`
	const isProfile = type === "ProfilePage"
	const isPages = metadata.path === "/pages"
	const isDefaultImage = image === `${SITE_URL}/social-card.jpg`
	const imageDescription = isDefaultImage ? SITE_NAME : title
	const ogType =
		type === "BlogPosting" ? "article" : isProfile ? "profile" : "website"
	const graph = isErrorPage ? null : buildJsonLdGraph(metadata, url)

	useEffect(() => {
		document.documentElement.lang = language
	}, [language])

	return (
		<Head>
			<title key="title">{title}</title>
			<meta key="description" name="description" content={description} />
			<meta key="author" name="author" content={SITE_NAME} />
			<meta
				key="is-agentic-site-type"
				name="is-agentic-site-type"
				content="content"
			/>
			<meta
				key="robots"
				name="robots"
				content={
					noindex ? "noindex,follow" : "index,follow,max-image-preview:large"
				}
			/>
			{!isErrorPage && <link key="canonical" rel="canonical" href={url} />}
			<link
				key="rss"
				rel="alternate"
				type="application/rss+xml"
				title={`${SITE_NAME} RSS`}
				href={`${SITE_URL}/feed.xml`}
			/>
			<link
				key="llms-txt"
				rel="alternate"
				type="text/plain"
				title="LLMs summary"
				href={`${SITE_URL}/llms.txt`}
			/>
			{isProfile && (
				<link
					key="markdown-alternate"
					rel="alternate"
					type="text/markdown"
					title="Markdown profile"
					href={`${SITE_URL}/index.md`}
				/>
			)}
			{isPages && (
				<link
					key="markdown-alternate"
					rel="alternate"
					type="text/markdown"
					title="Markdown pages directory"
					href={`${SITE_URL}/pages.md`}
				/>
			)}
			{isProfile &&
				PERSON_IDENTITY.relMe.map((href) => (
					<link key={`me-${href}`} rel="me" href={href} />
				))}
			<meta key="og:type" property="og:type" content={ogType} />
			{!isErrorPage && <meta key="og:url" property="og:url" content={url} />}
			<meta key="og:title" property="og:title" content={title} />
			<meta
				key="og:description"
				property="og:description"
				content={description}
			/>
			<meta key="og:site_name" property="og:site_name" content={SITE_NAME} />
			<meta
				key="og:locale"
				property="og:locale"
				content={language === "zh-CN" ? "zh_CN" : "en_US"}
			/>
			{isProfile && (
				<meta
					key="profile:first_name"
					property="profile:first_name"
					content={PERSON_IDENTITY.givenName}
				/>
			)}
			{isProfile && (
				<meta
					key="profile:last_name"
					property="profile:last_name"
					content={PERSON_IDENTITY.familyName}
				/>
			)}
			{isProfile && (
				<meta
					key="profile:username"
					property="profile:username"
					content="ttttonyhe"
				/>
			)}
			<meta key="og:image" property="og:image" content={image} />
			{isDefaultImage && (
				<meta key="og:image:width" property="og:image:width" content="1012" />
			)}
			{isDefaultImage && (
				<meta key="og:image:height" property="og:image:height" content="506" />
			)}
			{isDefaultImage && (
				<meta
					key="og:image:type"
					property="og:image:type"
					content="image/jpeg"
				/>
			)}
			<meta
				key="og:image:alt"
				property="og:image:alt"
				content={imageDescription}
			/>
			<meta
				key="twitter:card"
				name="twitter:card"
				content="summary_large_image"
			/>
			<meta key="twitter:site" name="twitter:site" content="@ttttonyhe" />
			<meta key="twitter:creator" name="twitter:creator" content="@ttttonyhe" />
			<meta key="twitter:title" name="twitter:title" content={title} />
			<meta
				key="twitter:description"
				name="twitter:description"
				content={description}
			/>
			<meta key="twitter:image" name="twitter:image" content={image} />
			<meta
				key="twitter:image:alt"
				name="twitter:image:alt"
				content={imageDescription}
			/>
			{datePublished && (
				<meta
					key="article:published_time"
					property="article:published_time"
					content={datePublished}
				/>
			)}
			{type === "BlogPosting" && (
				<meta
					key="article:author"
					property="article:author"
					content={`${SITE_URL}/`}
				/>
			)}
			{graph && (
				<script
					key="json-ld"
					type="application/ld+json"
					dangerouslySetInnerHTML={{
						__html: JSON.stringify(graph).replaceAll("<", "\\u003c"),
					}}
				/>
			)}
		</Head>
	)
}
