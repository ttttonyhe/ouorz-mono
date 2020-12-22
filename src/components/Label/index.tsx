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
        <label className="justify-center items-center flex w-auto h-auto py-1 px-3 bg-yellow-200 hover:bg-yellow-300 text-center rounded-md text-label tracking-wide text-yellow-500">
          <span className="w-7 h-7">{Icons.sticky}</span>
        </label>
      )
    case 'primary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center flex w-auto px-4 py-1 bg-blue-100 hover:bg-blue-200 text-center rounded-md text-label tracking-wide text-blue-500">
          {icon && <span className="w-8 h-8 mr-2">{Icons[icon]}</span>}
          {children}
        </label>
      )
    case 'secondary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center h-full flex w-auto px-4 py-1 pb-pre bg-gray-100 hover:bg-gray-200 text-center rounded-md text-label tracking-wide text-gray-500">
          {icon && <span className="w-7 h-7 mr-2">{Icons[icon]}</span>}
          {children}
        </label>
      )
  }
}
