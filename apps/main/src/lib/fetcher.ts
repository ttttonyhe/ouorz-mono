const host =
	process.env.NODE_ENV === 'development'
		? 'http://localhost:3333'
		: 'https://www.ouoz.com'

const Fetcher = async (route: string) => {
	const res = await fetch(`${host}${route}`)
	return res.json()
}

export default Fetcher
