import { useRouter } from 'next/router'
import { NexmentContainer } from 'nexment'

const Nexment = () => {
	const router = useRouter()
	const config = {
		pageKey: router.asPath.split('/')[2].toString(),
		enableLinkInput: true,
		enableReplyListModal: true,
		descriptionTag: false,
		leancloud: {
			appId: process.env.NEXT_PUBLIC_LC_ID,
			appKey: process.env.NEXT_PUBLIC_LC_KEY,
			serverURL: 'https://ouorz-nexment.ouorz.com',
		},
		admin: {
			name: 'TonyHe',
			email: 'tony.hlp@hotmail.com',
		},
		blackList: [
			{
				name: '快递',
				keyword: '代发',
				link: '88danhaowang.com',
				email: '461646@qq.com',
			},
			{
				name: 'rthj',
				keyword: '单号',
				link: 'dh5u.com',
			},
			{
				keyword: '快递',
			},
			{
				keyword: '空包',
			},
			{
				keyword: '快递',
			},
			{
				keyword: '快发',
			},
			{
				keyword: '快单',
			},
			{
				keyword: '一毛钱',
			},
		],
	}
	if (process.env.NEXT_PUBLIC_LC_ID && process.env.NEXT_PUBLIC_LC_KEY) {
		return <NexmentContainer config={config} />
	} else {
		return <></>
	}
}

export default Nexment
