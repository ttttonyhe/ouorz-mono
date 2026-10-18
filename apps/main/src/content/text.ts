export type ContentLanguage = "en" | "zh-CN"

export const getContentLanguage = (
	text: string,
	language?: ContentLanguage
): ContentLanguage =>
	language ?? (/\p{Script=Han}/u.test(text) ? "zh-CN" : "en")

export const isArticleContent = (content: string, link?: string) =>
	!link && content.trim().length > 0
