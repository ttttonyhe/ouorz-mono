import dynamic from 'next/dynamic'
import Icons from '~/components/Icons'
import { GetServerSideProps } from 'next'
const NexmentDiv = dynamic(() => import('~/components/Nexment'), {
  ssr: false,
})

export default function CommentBox({ id, key }: { id: string; key: string }) {
  return (
    <div className="lg:mt-5 bg-white dark:bg-gray-800 dark:border-gray-800 p-5 lg:py-11 lg:px-20 lg:shadow-sm lg:border lg:rounded-xl">
      <div className="mb-8">
        <h1 className="flex text-3xl font-medium text-gray-700 dark:text-white tracking-wide items-center">
          <span className="w-9 h-9 mr-2">{Icons.comments}</span>Comments
        </h1>
        <p className="text-xl tracking-wide text-gray-500 dark:text-gray-400 mt-2 mb-5">
          Leave a comment to join the discussion
        </p>
      </div>
      <NexmentDiv id={id} key={key} />
    </div>
  )
}

export const getServerSideProps: GetServerSideProps = async () => {
  return {
    props: {
      id: process.env.LC_ID,
      key: process.env.LC_KEY,
    },
  }
}
