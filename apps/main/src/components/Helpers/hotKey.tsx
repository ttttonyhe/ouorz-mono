import { useHotkeys } from "react-hotkeys-hook"
import type { KbarListItem } from "../Kbar"

/**
 *  Helper component to use react-hotkeys-hook to register hotkeys
 *  for Kbar list items
 *
 * @param {{ item: KbarListItem }} { item }
 * @return {*}
 */
const HotkeyHelper = ({ item }: { item: KbarListItem }) => {
	useHotkeys(
		`shift+${item.shortcut.join("+")}`,
		(e) => {
			// Only trigger if shift key is actually pressed
			if (e.shiftKey) {
				e.preventDefault()
				item.action()
			}
		},
		{
			enableOnFormTags: ["INPUT"],
			useKey: true,
		}
	)
	// render nothing
	return null
}

export default HotkeyHelper
