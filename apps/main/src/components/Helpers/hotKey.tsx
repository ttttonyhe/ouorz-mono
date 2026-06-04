import { useHotkeys } from "react-hotkeys-hook"

const HotkeyHelper = ({
	onTrigger,
	shortcut,
}: {
	onTrigger: () => void
	shortcut: string[]
}) => {
	const hotkey = `shift+${shortcut.join("+")}`

	useHotkeys(
		hotkey,
		(e) => {
			if (e.shiftKey) {
				e.preventDefault()
				onTrigger()
			}
		},
		{
			enableOnFormTags: ["INPUT"],
			useKey: true,
		},
		[onTrigger]
	)
	return null
}

export default HotkeyHelper
