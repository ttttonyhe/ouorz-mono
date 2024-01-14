import getApi from "~/utilities/api"

function generateSiteMap(postIDs: number[]) {
	return `<?xml version="1.0" encoding="UTF-8"?>
   <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     ${postIDs
				.map((id) => {
					return `
       <url>
           <loc>${`https://www.ouorz.com/${id}`}</loc>
           <changefreq>monthly</changefreq>
           <priority>0.6</priority>
       </url>
     `
				})
				.join("")}
   </urlset>
 `
}

function SiteMap() {}

export async function getServerSideProps({ res }) {
	const request = await fetch(getApi({ searchIndexes: true }))
	const indexes = await request.json()

	const postIDs: number[] = indexes["ids"]
	const sitemap = generateSiteMap(postIDs)

	res.setHeader("Content-Type", "text/xml")
	res.write(sitemap)
	res.end()

	return {
		props: {},
	}
}

export default SiteMap
