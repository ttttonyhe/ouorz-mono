import { ReactNode } from "react"

const RootLayout = ({ children }: { children: ReactNode }) => {
	return (
		<html lang="en">
			<body>{children}</body>
		</html>
	)
}

export default RootLayout
