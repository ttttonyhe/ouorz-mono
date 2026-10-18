import Head from "next/head"
import { useEffect } from "react"

import {
	PERSON_ID,
	PROFILE_DESCRIPTION,
	SITE_NAME,
	SITE_URL,
	type PageMetadata,
} from "~/utilities/seo"

interface Props {
	metadata: PageMetadata
}

export default function SiteSeo({ metadata }: Props) {
	const { title, description, language, type, image, datePublished, noindex } =
		metadata
	const url = `${SITE_URL}${metadata.path}`
	const isProfile = type === "ProfilePage"
	const isDefaultImage = image === `${SITE_URL}/social-card.jpg`
	const imageDescription = isDefaultImage ? SITE_NAME : title
	const person = {
		"@type": "Person",
		"@id": PERSON_ID,
		name: "Lipeng He",
		alternateName: ["Tony He", "Tony (Lipeng) He", "贺莉朋"],
		url: `${SITE_URL}/`,
		image: `${SITE_URL}/profile.jpg`,
		description: PROFILE_DESCRIPTION,
		jobTitle: "PhD student in computer science",
		affiliation: {
			"@type": "CollegeOrUniversity",
			name: "University of Waterloo",
			url: "https://uwaterloo.ca/",
		},
		sameAs: [
			"https://scholar.google.com/citations?user=6yFlE_sAAAAJ",
			"https://orcid.org/0009-0002-1802-7394",
			"https://github.com/ttttonyhe",
			"https://www.linkedin.com/in/~lhe",
			"https://x.com/ttttonyhe",
			"https://www.youtube.com/@ttttonyhe",
		],
	}
	const website = {
		"@type": "WebSite",
		"@id": `${SITE_URL}/#website`,
		url: `${SITE_URL}/`,
		name: SITE_NAME,
		inLanguage: ["en", "zh-CN"],
		publisher: { "@id": PERSON_ID },
	}
	const page = {
		"@type": type,
		"@id": `${url}#webpage`,
		url,
		name: title,
		headline: title,
		description,
		inLanguage: language,
		isPartOf: { "@id": website["@id"] },
		author: { "@id": PERSON_ID },
		mainEntity: isProfile ? { "@id": PERSON_ID } : undefined,
		datePublished,
		image,
	}
	const graph = {
		"@context": "https://schema.org",
		"@graph": [person, website, page],
	}

	useEffect(() => {
		document.documentElement.lang = language
	}, [language])

	return (
		<Head>
			<title>{title}</title>
			<meta name="description" content={description} />
			<meta name="author" content={SITE_NAME} />
			<meta
				name="robots"
				content={
					noindex ? "noindex,follow" : "index,follow,max-image-preview:large"
				}
			/>
			<link rel="canonical" href={url} />
			<link
				rel="alternate"
				type="application/rss+xml"
				title={`${SITE_NAME} RSS`}
				href={`${SITE_URL}/feed.xml`}
			/>
			<meta
				property="og:type"
				content={type === "BlogPosting" ? "article" : "website"}
			/>
			<meta property="og:url" content={url} />
			<meta property="og:title" content={title} />
			<meta property="og:description" content={description} />
			<meta property="og:site_name" content={SITE_NAME} />
			<meta
				property="og:locale"
				content={language === "zh-CN" ? "zh_CN" : "en_US"}
			/>
			<meta property="og:image" content={image} />
			{isDefaultImage && <meta property="og:image:width" content="1012" />}
			{isDefaultImage && <meta property="og:image:height" content="506" />}
			{isDefaultImage && <meta property="og:image:type" content="image/jpeg" />}
			<meta property="og:image:alt" content={imageDescription} />
			<meta name="twitter:card" content="summary_large_image" />
			<meta name="twitter:site" content="@ttttonyhe" />
			<meta name="twitter:creator" content="@ttttonyhe" />
			<meta name="twitter:title" content={title} />
			<meta name="twitter:description" content={description} />
			<meta name="twitter:image" content={image} />
			<meta name="twitter:image:alt" content={imageDescription} />
			{datePublished && (
				<meta property="article:published_time" content={datePublished} />
			)}
			<script
				type="application/ld+json"
				dangerouslySetInnerHTML={{
					__html: JSON.stringify(graph).replaceAll("<", "\\u003c"),
				}}
			/>
		</Head>
	)
}
