'use client'

import { useEffect } from 'react'
import getApi from '~/utilities/api'

const PageView = ({ id }: { id: string }) => {
	useEffect(() => {
		fetch(
			getApi({
				visit: parseInt(id),
			})
		).catch((err) => {
			console.error(err)
		})
	}, [])

	return null
}

export default PageView
