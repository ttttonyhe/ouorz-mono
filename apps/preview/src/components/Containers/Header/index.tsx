import responsive from "@/styles/responsive.module.css"
import cn from "clsx"

const Header = () => {
	return (
		<header className="relative z-header flex h-header">
			<div
				className={cn(
					responsive["sidebar-width"],
					"sidebar flex-shrink-0 bg-yellow-500"
				)}>
				<h1>Tony He</h1>
			</div>
			<div className="w-full bg-blue-500">
				<span>k-bar</span>
			</div>
		</header>
	)
}

export default Header
