import { type Dispatch, type SetStateAction, useEffect, useState } from "react"

type BodyProperty = "overflow" | "pointerEvents"

const locks = new Map<BodyProperty, { count: number; previous: string }>()

const useBodyProperty = (
	property: BodyProperty,
	lockedValue: string
): [boolean, Dispatch<SetStateAction<boolean>>] => {
	const [enabled, setEnabled] = useState(true)

	useEffect(() => {
		if (enabled) return

		const body = document.body
		const lock = locks.get(property) ?? {
			count: 0,
			previous: body.style[property],
		}
		lock.count += 1
		locks.set(property, lock)
		body.style[property] = lockedValue

		return () => {
			lock.count -= 1
			if (lock.count === 0) {
				body.style[property] = lock.previous
				locks.delete(property)
			}
		}
	}, [enabled, lockedValue, property])

	return [enabled, setEnabled]
}

const useBodyScroll = () => useBodyProperty("overflow", "hidden")
const useBodyPointerEvents = () => useBodyProperty("pointerEvents", "none")

export { useBodyScroll, useBodyPointerEvents }
