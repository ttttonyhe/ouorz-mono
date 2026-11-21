import type { Metadata } from "next"
import type { FC, PropsWithChildren } from "react"

export const metadata: Metadata = {
	title: "Twilight Protocol Interface",
	description: "On-chain contract interface for Twilight Blog.",
}

const RootLayout: FC<PropsWithChildren> = ({ children }) => {
	return (
		<html lang="en">
			<body>{children}</body>
		</html>
	)
}

export default RootLayout
