export const trimStr = (str: string, n: number) => {
	if (str.replaceAll(/[\u4E00-\u9FA5]/gu, "**").length <= n) {
		return str
	} else {
		let len = 0
		let tmpStr = ""
		for (let i = 0; i < str.length; i++) {
			if (/[\u4E00-\u9FA5]/u.test(str[i])) {
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
		return `${tmpStr.replace(" ", "")} ...`
	}
}
