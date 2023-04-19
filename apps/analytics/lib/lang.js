import { enUS, zhCN } from "date-fns/locale"

export const languages = {
	"zh-CN": { label: "中文", dateLocale: zhCN },
	"en-US": { label: "English (US)", dateLocale: enUS },
}

export function getDateLocale(locale) {
	return languages[locale]?.dateLocale || enUS
}

export function getTextDirection(locale) {
	return languages[locale]?.dir || "ltr"
}
