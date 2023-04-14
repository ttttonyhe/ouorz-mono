/* eslint-disable camelcase */
import { tuple } from '~/utilities/dataTypes'

const listTypes = tuple('index', 'cate', 'search')
const labelTypes = tuple('sticky', 'primary', 'secondary', 'green', 'gray')

export interface WPPost {
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
		link?: string
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

export type ListTypes = (typeof listTypes)[number]
export type LabelTypes = (typeof labelTypes)[number]
