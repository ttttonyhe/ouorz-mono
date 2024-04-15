import { EST_SINCE } from "@/constants/info"

const Footer = () => {
	const currentYear = new Date().getFullYear()

	return (
		<footer className="relative z-footer flex h-footer border-t dark:border-neutral-800 dark:bg-neutral-900">
			<div>
				<p>
					&copy; {EST_SINCE} - {currentYear} Tony He
				</p>
			</div>
		</footer>
	)
}

export default Footer
