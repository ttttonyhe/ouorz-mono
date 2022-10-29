import React from 'react'
import PageCard from '~/components/Card/Page'

const PagesPage = () => {
	return (
		<div className="glowing-area mt-5 grid grid-cols-2 gap-4">
			<PageCard
				title="Dashboard"
				des="Track my metrics"
				icon="ppt"
				className="text-blue-500"
				href="/dashboard"
			/>
			<PageCard
				title="Guestbook"
				des="Leave your comments"
				icon="chat"
				className="text-green-400"
				href="/page/249"
			/>
			<PageCard
				title="AMA"
				des="Ask me anything"
				icon="question"
				className="text-yellow-400"
				href="/page/765"
			/>
			<PageCard
				title="Links"
				des="Some of my friends"
				icon="people"
				className="text-pink-400"
				href="/friends"
			/>
			<PageCard
				title="Thoughts"
				des="Random but memorable"
				icon="lightBulb"
				className="text-red-400"
				href="https://notion.ouorz.com"
			/>
			<PageCard
				title="Analytics"
				des="Visitor statistics"
				icon="growth"
				className="text-blue-400"
				href="https://analytics.ouorz.com/share/E4O9QpCn/ouorz-next"
			/>
			<PageCard
				title="Podcast"
				des="Known Unknowns"
				icon="mic"
				className="text-gray-600"
				href="https://kukfm.com"
			/>
			<PageCard
				title="Snapod"
				des="Podcast hosting platform"
				icon="microphone"
				className="text-gray-500"
				href="https://www.snapodcast.com"
			/>
		</div>
	)
}

export default PagesPage
