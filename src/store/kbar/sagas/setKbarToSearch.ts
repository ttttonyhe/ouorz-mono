import { call, put } from 'redux-saga/effects'
import {
	setKbarCurrentListByKey,
	addToKbarLists,
	setKbarLoading,
	updateKbarLocation,
	setKbarPlaceholder,
} from '../actions'
import Fetcher from '~/lib/fetcher'
import getApi from '~/utilities/api'

export default function* setKbarToSearchSaga() {
	try {
		// start loading
		yield put(setKbarLoading(true))
		// set kbar location to search
		yield put(updateKbarLocation(['home', 'search']))
		// set kbar placeholder
		yield put(setKbarPlaceholder('Search articles...'))
		// fetch search index data
		const searchData = yield call(
			Fetcher,
			getApi({
				searchIndexes: true,
			})
		)
		// construct search index list data
		const searchList = searchData.ids.map((id: number, index: number) => {
			const title = searchData.titles[index]
			return {
				label: title,
				link: {
					internal: `/post/${id}`,
				},
				onClick: () => {
					// terminateSearch()
					window.location.href = `https://www.ouorz.com/post/${id}`
				},
				className: 'w-full !justify-start !p-4',
			}
		})
		// add search index list to kbar lists
		yield put(addToKbarLists('search', searchList))
		// set search index list data
		yield put(setKbarCurrentListByKey('search'))
		// stop loading
		yield put(setKbarLoading(false))
	} catch (error) {
		console.error(error)
	}
}
