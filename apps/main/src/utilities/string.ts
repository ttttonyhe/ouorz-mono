const CJK_CHARACTER = /[\u4E00-\u9FA5]/u

export const trimStr = (str: string, n: number): string => {
	if (str.replaceAll(/[\u4E00-\u9FA5]/gu, "**").length <= n) {
		return str
	}

	let len = 0
	let tmpStr = ""
	for (const char of str) {
		len += CJK_CHARACTER.test(char) ? 2 : 1
		if (len > n) break
		tmpStr += char
	}
	return `${tmpStr.replace(" ", "")} ...`
}
