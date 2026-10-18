import { select, type SelectEffect } from "redux-saga/effects"

import type { RootState } from ".."

const selectKbar = (state: RootState) => state.kbar

function* selectKbarState(): Generator<
	SelectEffect,
	ReturnType<typeof selectKbar>,
	ReturnType<typeof selectKbar>
> {
	return yield select(selectKbar)
}

export { selectKbar, selectKbarState }
