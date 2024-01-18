import stripAnsi from "strip-ansi"

export const trimStr = (str: string, n: number) => {
	if (str.replace(/[\u4e00-\u9fa5]/g, "**").length <= n) {
		return str
	} else {
		let len = 0
		let tmpStr = ""
		for (let i = 0; i < str.length; i++) {
			if (/[\u4e00-\u9fa5]/.test(str[i])) {
				len += 2
			} else {
				len += 1
			}
			if (len > n) {
				break
			} else {
				tmpStr += str[i]
			}
		}
		return tmpStr.replace(" ", "") + " ..."
	}
}

export const sanitizeStr = (str: string) => {
	return stripAnsi(
		str.replace(
			// eslint-disable-next-line no-control-regex
			/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007f-\u0084\u0086-\u009f\uD800-\uDFFF\uFDD0-\uFDFF\uFFFF\uC008]/g,
			""
		)
	)
}
