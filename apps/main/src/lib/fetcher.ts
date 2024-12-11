const Fetcher = async (route: string) => {
	return fetch(route, {
		next: {
			revalidate: 24 * 3600,
		},
	})
		.then((res) => res.json())
		.catch((err) => {
			console.error(err)
		})
}

export default Fetcher
