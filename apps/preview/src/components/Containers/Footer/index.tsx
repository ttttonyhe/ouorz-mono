import { EST_SINCE } from "@/constants/info"

const CURRENT_YEAR = new Date().getFullYear()

const Footer = () => (
	<footer className="relative z-footer flex h-footer border-t dark:border-neutral-800 dark:bg-neutral-900">
		<div>
			<p>
				&copy; {EST_SINCE} - {CURRENT_YEAR} Tony He
			</p>
		</div>
	</footer>
)

export default Footer
