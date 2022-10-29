type HeadingData = {
	title: string
	description: string
	note: string
	icon: string
}

const headingsData: {
	[pathname: string]: HeadingData
} = {
	'/friends': {
		title: 'Friends',
		description: "Tony's friends' sites",
		note: 'Email me at tony.hlp#hotmail.com for link exchange',
		icon: '🧑‍🤝‍🧑',
	},
	'/dashboard': {
		title: 'Dashboard',
		description: "Tony's personal dashboard",
		note: 'Personal dashboard to track metrics across various platforms',
		icon: '📊',
	},
	'/pages': {
		title: 'Pages',
		description: "Tony's special blog pages",
		note: '',
		icon: '📑',
	},
}

export default headingsData
