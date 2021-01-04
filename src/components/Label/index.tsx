import Icons from '~/components/Icons'

interface Props {
  name: string
  icon?: string
  children?: React.ReactNode
  preview?: boolean
}
export default function Label({ name, icon, children, preview }: Props) {
  switch (name) {
    case 'sticky':
      return (
        <label className="justify-center items-center flex w-auto h-auto xl:py-1 xl:px-3 py-0 px-2 bg-yellow-200 dark:bg-yellow-800 hover:bg-yellow-300 dark:hover:bg-yellow-700 text-center rounded-md text-4 xl:text-label tracking-wide text-yellow-500 align-middle">
          <span className="xl:w-7 xl:h-7 h-4 w-4">{Icons.sticky}</span>
        </label>
      )
    case 'primary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center flex w-auto xl:px-4 xl:py-1 px-2 py-1 bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-center rounded-md text-4 xl:text-label tracking-wide text-blue-500 dark:text-blue-300 align-middle">
          {icon && (
            <span className="xl:w-7 xl:h-7 h-4 w-4 xl:mr-2 mr-1">
              {Icons[icon]}
            </span>
          )}
          {children}
        </label>
      )
    case 'secondary':
      return (
        <label className="cursor-pointer justify-center font-medium items-center flex w-auto xl:px-4 px-2 py-1 xl:py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-center rounded-md text-4 xl:text-label tracking-wide text-gray-500 dark:text-gray-300 align-middle">
          {icon && (
            <span className="xl:w-7 xl:h-7 h-4 w-4 xl:mr-2 mr-1">
              {Icons[icon]}
            </span>
          )}
          {children}
        </label>
      )
    case 'green':
      return (
        <label
          className={`group cursor-pointer justify-center font-medium items-center h-full flex w-min ${
            preview ? 'px-3 py-0.5' : 'px-4 py-1.5'
          } bg-green-100 dark:bg-green-800 hover:bg-green-200 dark:hover:bg-green-700 text-center rounded-md text-xl tracking-wide text-green-500 dark:text-green-400 align-middle`}
        >
          {children}
          {icon && (
            <span className="w-5 h-5 ml-1 group-hover:animate-pointer">
              {Icons[icon]}
            </span>
          )}
        </label>
      )
    case 'gray':
      return (
        <label className="cursor-pointer justify-center font-medium items-center h-full flex w-min px-2 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-center rounded-md text-xl tracking-wide text-gray-500 dark:text-gray-300 align-middle">
          {icon && <span className="w-7 h-7">{Icons[icon]}</span>}
        </label>
      )
  }
}
