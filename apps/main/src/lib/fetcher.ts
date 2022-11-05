/**
 * A wrapper function of fetch() that autocomples the full URL
 *
 * @param {string} path
 * @return {*} {Promise<any>}
 */
const Fetcher = async (path: string): Promise<any> => {
	const url = path.startsWith('http')
		? path
		: `${process.env.NEXT_PUBLIC_HOST_URL}/${path}`
	const res = await fetch(url)
	return res.json()
}

export default Fetcher
