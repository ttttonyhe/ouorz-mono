import { KbarListItem } from '../Kbar'
import { useHotkeys } from 'react-hotkeys-hook'

/**
 *  Helper component to use react-hotkeys-hook to register hotkeys
 *  for Kbar list items
 *
 * @param {{ item: KbarListItem }} { item }
 * @return {*}
 */
const HotkeyHelper = ({ item }: { item: KbarListItem }): null => {
	useHotkeys(
		`shift+${item.shortcut.join('+')}`,
		() => item.action(),
		{
			enableOnFormTags: ['INPUT'],
			preventDefault: true,
		},
		[item]
	)
	// render nothing
	return null
}

export default HotkeyHelper
