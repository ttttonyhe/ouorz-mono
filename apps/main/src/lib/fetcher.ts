const DEV_URL = 'http://localhost:3333'
const PROD_URL = 'https://www.ouorz.com'

const Fetcher = async (route: string) => {
	let url = ''
	if (route.includes('http')) {
		url = route
	} else {
		url = `${
			process.env.NODE_ENV !== 'production' ? DEV_URL : PROD_URL
		}${route}`
	}

	const res = await fetch(url)
	return res.json()
}

export default Fetcher
