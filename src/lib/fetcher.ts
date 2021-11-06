export default async function Fetcher(route: string) {
	const res = await fetch(route)
	return res.json()
}
