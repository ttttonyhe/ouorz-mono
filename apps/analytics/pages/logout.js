import { useEffect } from "react"
import { useRouter } from "next/router"
import { removeItem } from "lib/web"
import { AUTH_TOKEN } from "lib/constants"
import { setUser } from "store/app"

export default function LogoutPage() {
	const router = useRouter()

	useEffect(() => {
		removeItem(AUTH_TOKEN)
		setUser(null)
		router.replace("/login")
	}, [router])

	return null
}
