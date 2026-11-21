const numberFormatter = new Intl.NumberFormat("en-US")

export const formatNumber = (value: number): string =>
	numberFormatter.format(value)
