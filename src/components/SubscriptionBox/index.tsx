import React from 'react'
import Icons from '~/components/Icons'
import Button from '~/components/Button'
import { getApi } from '~/utilities/Api'

export default function SubscriptionBox({ type }: { type: string }) {
  const [email, setEmail] = React.useState<string>('')
  const [subscribed, setSubscribed] = React.useState<boolean>(false)
  const [processing, setProcessing] = React.useState<boolean>(false)
  const doSubscribe = async () => {
    setProcessing(true)

    const res = await fetch(getApi({ subs: true }), {
      method: 'post',
      headers: {
        'Content-Type': 'application/json',
        Authorization: 'Token 110279' + '82-828a-4e06' + '-bd0f-c2566a65a5e7',
      },
      body: JSON.stringify({ email: email }),
    })
    const data = await res.json()

    setProcessing(false)
    if (data.creation_date) {
      setSubscribed(true)
    } else {
      alert('An error has occurred')
    }
  }
  if (type === 'sm') {
    return (
      <div className="border shadow-sm w-full py-3 px-5 flex rounded-md bg-white items-center my-2 space-x-4">
        <div>
          <p className="text-xl tracking-wide text-gray-500 whitespace-nowrap items-center flex">
            <span className="w-7 h-7 mr-2">{Icons.subscribe}</span>Get post
            updates straight to your inbox
          </p>
        </div>
        <div className="flex justify-end w-full">
          {subscribed ? (
            <div className="bg-green-500 w-10/12 py-1.5 text-4 rounded-md text-center text-white">
              Succeed
            </div>
          ) : (
            <input
              type="email"
              value={email}
              className={`${
                processing ? 'animate-pulse' : ''
              } text-4 px-3 py-1.5 focus:outline-none w-10/12 shadow-sm rounded-md border bg-white text-gray-500 tracking-wide`}
              placeholder="Email address"
              onChange={(e) => {
                setEmail(e.target.value)
              }}
              onKeyPress={(e) => {
                if (e.key === 'Enter') {
                  doSubscribe()
                }
              }}
            ></input>
          )}
        </div>
      </div>
    )
  } else {
    return (
      <div className="border shadow-sm w-full p-10 lg:py-11 lg:px-20 rounded-xl bg-white items-center my-2">
        <div>
          <h1 className="flex text-3xl font-medium text-gray-700 tracking-wide items-center">
            <span className="w-9 h-9 mr-2">{Icons.subscribe}</span>Subscribe
          </h1>
          <p className="text-xl tracking-wide text-gray-500 mt-2 mb-5">
            Get post updates straight to your inbox
          </p>
        </div>
        <div className="w-full grid grid-cols-3 gap-3 rounded-md bg-white text-gray-500 tracking-wide">
          <input
            type="email"
            value={email}
            className="col-start-1 col-end-3 w-full text-4 rounded-md px-3 py-1.5 focus:outline-none shadow-sm border"
            placeholder="Email address"
            onChange={(e) => {
              setEmail(e.target.value)
            }}
          ></input>
          {subscribed ? (
            <div className="bg-green-500 col-start-3 col-end-4 text-4 py-3 rounded-md text-center text-white">
              Succeed
            </div>
          ) : (
            <Button
              bType="primary"
              type="submit"
              className="col-start-3 col-end-4 text-4"
              onClick={() => {
                doSubscribe()
              }}
            >
              {processing ? 'Processing...' : 'Subscribe'}
            </Button>
          )}
        </div>
      </div>
    )
  }
}
