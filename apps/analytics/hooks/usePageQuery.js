import { useMemo } from "react"
import { useRouter } from "next/router"
import { getQueryString } from "lib/url"

export default function usePageQuery() {
	const router = useRouter()
	const { pathname, search } = new URL(router.asPath, "http://localhost")

	const query = useMemo(
		() => Object.fromEntries(new URLSearchParams(search)),
		[search]
	)

	function resolve(params) {
		const search = getQueryString({ ...query, ...params })

		return `${pathname}${search}`
	}

	return { pathname, query, resolve, router }
}
