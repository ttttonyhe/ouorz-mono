const scrollToItemWithinDiv = (
	parent: HTMLElement | Element,
	item: HTMLElement | Element
) => {
	const parentRect = parent.getBoundingClientRect()
	const childRect = item.getBoundingClientRect()
	const isViewable =
		childRect.top >= parentRect.top &&
		childRect.bottom <= parentRect.top + parent.clientHeight

	if (!isViewable) {
		const scrollTop = childRect.top - parentRect.top
		const scrollBot = childRect.bottom - parentRect.bottom
		const delta =
			Math.abs(scrollTop) < Math.abs(scrollBot)
				? scrollTop - 100
				: scrollBot + 100
		parent.scrollTo({
			top: parent.scrollTop + delta,
			behavior: "smooth",
		})
	}
}

export default scrollToItemWithinDiv
