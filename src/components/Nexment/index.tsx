import { useRouter } from 'next/router'
import { NexmentContainer } from 'nexment'

const Nexment = () => {
  const router = useRouter()
  const config = {
    pageKey: router.asPath.split('/')[2].toString(),
    enableLinkInput: true,
    enableReplyListModal: false,
    leancloud: {
      appId: 'NM8cdTVi8wqCmbeLPmiKCu79-gzGzoHsz',
      appKey: 'p31o8YmzTfjBY68W2Y9gH3kb',
      serverURL: 'https://ouorz-nexment.ouorz.com',
    },
    admin: {
      name: 'TonyHe',
      email: 'he@holptech.com',
    },
  }
  return <NexmentContainer config={config} />
}

export default Nexment
