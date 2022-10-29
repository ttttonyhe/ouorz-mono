import React from 'react'

export {}

declare global {
	type LayoutProps = {
		children: React.ReactNode
	}

	interface Window {
		// ouorz-analytics tracker
		ouorzAnalytics: {
			trackView: (url?: string, referrer?: string, uuid?: string) => void
			trackEvent: (
				event_value: string,
				event_type?: string,
				url?: string,
				uuid?: string
			) => void
		}
	}

	interface WPCate {
		name: string
		count: number
		id: number
	}

	interface WPPost {
		code: any
		post_img: { url: any }
		post_title: string
		post_excerpt: {
			four: string
		}
		post_categories: { term_id: number; name: string }[]
		id: string
		title: {
			rendered: string
		}
		date: Date
		post_metas: {
			link: string
			status: string
			markCount: number
			views: number
			reading: {
				word_count: number
				time_required: number
			}
			fineTool: {
				itemImgBorder: string
				itemName: string
				itemDes: string
				itemLink: string
				itemLinkName: string
			}
			podcast: {
				episode: string
				episodeUrl: string
				audioUrl: string
			}
		}
		content: {
			rendered: string
		}
	}
}
