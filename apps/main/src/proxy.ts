import { type NextRequest, NextResponse } from "next/server"

const proxy = (req: NextRequest): NextResponse => {
	const url = req.nextUrl.clone()
	url.pathname = url.pathname.slice("/assets".length)
	return NextResponse.rewrite(url)
}

export const config = { matcher: "/assets/_next/:path*" }

export default proxy
