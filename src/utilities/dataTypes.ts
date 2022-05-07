export const tuple = <T extends string[]>(...args: T) => args

// Utility Types
export type StringLiteral<T> = T extends string
	? string extends T
		? never
		: T
	: never

export interface ReduxActionWithPayload<T extends string> {
	type: T
}

export interface ReduxActionWithoutPayload<T extends string> {
	type: T
	payload: null
}
