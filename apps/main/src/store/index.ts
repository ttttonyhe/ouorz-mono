import reducer from "./reducers"
import saga from "./sagas"
import { configureStore } from "@reduxjs/toolkit"
import createSagaMiddleware from "redux-saga"

// create the saga middleware
const sagaMiddleware = createSagaMiddleware({
	onError: (e) => {
		console.log(e)
	},
})

// mount it on the Store
const store = configureStore({
	reducer,
	middleware: (getDefaultMiddleware) => {
		return getDefaultMiddleware({
			immutableCheck: false,
			serializableCheck: false,
		}).concat(sagaMiddleware)
	},
})

// then run the saga
sagaMiddleware.run(saga)

// export the types for selectors and dispatch
export type RootState = ReturnType<typeof store.getState>
export type AppDispatch = typeof store.dispatch

export default store
