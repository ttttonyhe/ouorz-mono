const openLink = (link: string) => {
	if (typeof window !== "undefined") {
		window.open(link, "_blank", "noopener,noreferrer")
	}
}

export default openLink
