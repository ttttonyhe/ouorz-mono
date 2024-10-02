import { FC } from "react"
import getAPI from "~/utilities/api"

const SiteMap: FC = () => null

export const getServerSideProps = async ({ res }) => {
	const request = await fetch(getAPI("internal", "searchIndices"))
	const indexes = await request.json()

	const postIDs: number[] = indexes["ids"]
	const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		${postIDs
			.map((id) => {
				return `
			<url>
					<loc>${`https://lipeng.ac/${id}`}</loc>
					<changefreq>monthly</changefreq>
					<priority>0.6</priority>
			</url>
		`
			})
			.join("")}
	</urlset>
`

	res.setHeader("Content-Type", "text/xml")
	res.write(sitemap)
	res.end()

	return {
		props: {},
	}
}

export default SiteMap
