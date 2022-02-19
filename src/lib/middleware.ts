/**
 * Middleware Initator
 *
 * @description Wait for a middleware to execute before continuing and to throw
 * an error when an error happens in a middleware
 * @param {*} middleware
 * @return {Function}
 */
const init = (middleware: any): ((req: any, res: any) => Promise<any>) => {
	return (req: any, res: any) =>
		new Promise((resolve, reject) => {
			middleware(req, res, (result) => {
				if (result instanceof Error) {
					return reject(result)
				}
				return resolve(result)
			})
		})
}

/**
 * Middleware Installer
 *
 * @param {*} middleware
 * @return {*}  {Promise<void>}
 */
const use = async (middleware: any, req: any, res: any): Promise<void> => {
	const initiation = init(middleware)
	await initiation(req, res)
}

export { init, use }
