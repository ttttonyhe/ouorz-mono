import { create } from "zustand"
import app from "./app"
import { getDateRange } from "../lib/date"

const store = create(() => ({}))

export function setDateRange(websiteId, value) {
	store.setState((state) => {
		const dateRange =
			typeof value === "string"
				? getDateRange(value, app.getState().locale)
				: value

		return {
			...state,
			[websiteId]: {
				...state[websiteId],
				dateRange: { ...dateRange, modified: Date.now() },
			},
		}
	})
}

export default store
