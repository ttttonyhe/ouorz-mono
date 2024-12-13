import { RawPostData } from "@/database"

const toLocalDate = (data: RawPostData) => {
	const date = new Date(data.date)
	const localDate = new Date(
		date.getTime() + date.getTimezoneOffset() * 60 * 1000
	)

	return {
		...data,
		date: localDate,
	}
}

export default toLocalDate
