const Fetcher = async (route: string) => {
	const res = await fetch(route)
	return res.json()
}

export default Fetcher
