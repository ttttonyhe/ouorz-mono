const Fetcher = async (route: string) => {
	const res = await fetch(`${process.env.NEXT_PUBLIC_HOST_URL}/${route}`)
	return res.json()
}

export default Fetcher
