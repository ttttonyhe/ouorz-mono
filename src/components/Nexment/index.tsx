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
      appId: process.env.LC_ID,
      appKey: process.env.LC_KEY,
      serverURL: 'https://ouorz-nexment.ouorz.com',
    },
    admin: {
      name: 'TonyHe',
      email: 'tony.hlp@hotmail.com',
    },
    blackList: [
      {
        name: '快递',
        content: '代发',
      },
      {
        name: '单号',
      },
    ],
  }
  return <NexmentContainer config={config} />
}

export default Nexment
