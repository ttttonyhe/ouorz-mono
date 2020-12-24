import React from 'react'
import Icons from '~/components/Icons'
import Button from '~/components/Button'

export default function SubscriptionBox({ type }: { type: string }) {
  const [email, setEmail] = React.useState<string>('')
  if (type === 'sm') {
    return (
      <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white items-center my-2">
        <div>
          <p className="text-xl tracking-wide text-gray-500 whitespace-nowrap items-center flex">
            <span className="w-7 h-7 mr-2">{Icons.subscribe}</span>Get post
            updates straight to your inbox
          </p>
        </div>
        <div className="w-full flex justify-end">
          <form
            action={`https://ouorz.us4.list-manage.com/subscribe/post?u=816e53482f0c1677fc41072af&id=3b6a276bad`}
            method="post"
            target="_blank"
            className="w-10/12 shadow-sm rounded-md border bg-white text-gray-500 tracking-wide"
          >
            <input
              type="email"
              value={email}
              name="EMAIL"
              className="text-lg w-full px-3 py-1.5 focus:outline-none transition-shadow hover:shadow-md"
              placeholder="Email address"
              onChange={(e) => {
                setEmail(e.target.value)
              }}
            ></input>
          </form>
        </div>
      </div>
    )
  } else {
    return (
      <div className="border shadow-sm w-full p-10  lg:py-12 lg:px-20 rounded-md bg-white items-center my-2">
        <div>
          <h1 className="flex text-3xl font-medium text-gray-700 tracking-wide items-center">
            <span className="w-9 h-9 mr-2">{Icons.subscribe}</span>Subscribe
          </h1>
          <p className="text-xl tracking-wide text-gray-500 mt-2 mb-8">
            Get post updates straight to your inbox
          </p>
        </div>
        <div className="w-full">
          <form
            action={`https://ouorz.us4.list-manage.com/subscribe/post?u=816e53482f0c1677fc41072af&id=3b6a276bad`}
            method="post"
            target="_blank"
            className="grid grid-cols-3 gap-3 w-full rounded-md bg-white text-gray-500 tracking-wide"
          >
            <input
              type="email"
              value={email}
              name="EMAIL"
              className="col-start-1 col-end-3 w-full text-lg rounded-md px-3 py-1.5 focus:outline-none transition-shadow shadow-sm border hover:shadow-md"
              placeholder="Email address"
              onChange={(e) => {
                setEmail(e.target.value)
              }}
            ></input>
            <Button
              bType="primary"
              type="submit"
              className="col-start-3 col-end-4"
            >
              Subscribe
            </Button>
          </form>
        </div>
      </div>
    )
  }
}
