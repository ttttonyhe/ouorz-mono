import { isBrowser } from "~/utilities/environment"

const openLink = (link: string) => {
	if (isBrowser()) {
		window.open(link, "_blank", "noopener,noreferrer")
	}
}

export default openLink
