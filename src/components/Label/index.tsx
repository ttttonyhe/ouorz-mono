import Icons from '~/components/Icons'

interface Props {
  name: string
  icon?: string
  children?: React.ReactNode
}
export default function Label({ name, icon, children }: Props) {
  switch (name) {
    case 'sticky':
      return (
        <label className="justify-center items-center flex w-auto h-auto py-1 px-3 bg-yellow-200 hover:bg-yellow-300 text-center rounded-md text-label tracking-wide text-yellow-500 align-middle">
          <span className="w-7 h-7">{Icons.sticky}</span>
        </label>
      )
    case 'primary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center flex w-auto px-4 py-1 bg-blue-100 hover:bg-blue-200 text-center rounded-md text-label tracking-wide text-blue-500 align-middle">
          {icon && <span className="w-8 h-8 mr-2">{Icons[icon]}</span>}
          {children}
        </label>
      )
    case 'secondary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-4 py-1 pb-pre bg-gray-100 hover:bg-gray-200 text-center rounded-md text-label tracking-wide text-gray-500 align-middle">
          {icon && <span className="w-7 h-7 mr-2">{Icons[icon]}</span>}
          {children}
        </label>
      )
    case 'green':
      return (
        <label className="group cursor-pointer justify-center font-medium items-center h-full flex w-min px-3 py-2 pb-pre bg-green-100 hover:bg-green-200 text-center rounded-md text-xl tracking-wide text-green-500 align-middle">
          {children}
          {icon && (
            <span className="w-6 h-6 ml-1 group-hover:animate-pointer">
              {Icons[icon]}
            </span>
          )}
        </label>
      )
    case 'gray':
      return (
        <label className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-2 py-2 bg-gray-100 hover:bg-gray-200 text-center rounded-md text-xl tracking-wide text-gray-500 align-middle">
          {icon && <span className="w-7 h-6">{Icons[icon]}</span>}
        </label>
      )
  }
}
