import React from 'react'
import Icons from '~/components/Icons'

interface Props {
  type: string
  icon?: string
  className?: string
  children?: React.ReactNode
}

export default function Button(props: Props) {
  const { type, icon, className, children } = props
  switch (type) {
    case 'default':
      return (
        <button
          className={
            className
              ? 'w-full py-2 px-7 shadow-sm border border-gray-300 hover:shadow-inner rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white flex ' +
                className
              : 'w-full py-2 px-7 shadow-sm border border-gray-300 hover:shadow-inner rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider bg-white flex'
          }
        >
          {icon && (
            <span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
              {Icons[icon]}
            </span>
          )}
          {children}
        </button>
      )
    case 'menu-default':
      return (
        <button
          className={
            className
              ? 'w-max py-2 px-5 hover:bg-menu rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-gray-500 ' +
                className
              : 'w-max py-2 px-5 hover:bg-menu rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-gray-500'
          }
        >
          {icon && (
            <span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
              {Icons[icon]}
            </span>
          )}
          {children}
        </button>
      )
    case 'primary':
      return (
        <button
          className={
            className
              ? 'w-full py-2 px-7 shadow-sm border border-blue-500 bg-blue-500 hover:bg-blue-600 hover:border-blue-600 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex ' +
                className
              : 'w-full py-2 px-7 shadow-sm border border-blue-500 bg-blue-500 hover:bg-blue-600 hover:border-blue-600 hover:shadow-inner text-white rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex'
          }
        >
          {icon && (
            <span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
              {Icons[icon]}
            </span>
          )}
          {children}
        </button>
      )
    case 'menu-primary':
      return (
        <button
          className={
            className
              ? 'w-max py-2 px-5 hover:bg-pink-100 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-pink-500 ' +
                className
              : 'w-max py-2 px-5 hover:bg-pink-100 rounded-md cursor-pointer focus:outline-none justify-center items-center text-xl tracking-wider flex text-pink-500'
          }
        >
          {icon && (
            <span className={children ? 'w-6 h-6 mr-1' : 'w-6 h-6'}>
              {Icons[icon]}
            </span>
          )}
          {children}
        </button>
      )
  }
}
