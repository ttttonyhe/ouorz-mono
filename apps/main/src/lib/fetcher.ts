const Fetcher = async (route: string) => {
	const res = await fetch(route, {
		next: {
			revalidate: 24 * 3600
		}
	})
	return res.json()
}

export default Fetcher
