'use client'

import { useEffect } from 'react'
import getApi from '~/utilities/api'

const PageView = ({ pid }: { pid: string }) => {
	useEffect(() => {
		fetch(
			getApi({
				visit: parseInt(pid),
			})
		).catch((err) => {
			console.error(err)
		})
	}, [])

	return null
}

export default PageView
