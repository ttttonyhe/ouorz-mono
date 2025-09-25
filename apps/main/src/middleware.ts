import { type NextRequest, NextResponse } from "next/server"

const middleware = (req: NextRequest): NextResponse => {
	if (req.nextUrl.pathname.startsWith("/assets/_next/")) {
		return NextResponse.rewrite(
			req.nextUrl.href.replace("/assets/_next/", "/_next/")
		)
	}
	return null
}

export default middleware
