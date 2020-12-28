import Icons from '~/components/Icons'
import { useRouter } from 'next/router'

interface Props {
  title: string
  des: string
  icon: string
  className?: string
  href: string
}

export default function PageCard({ title, des, icon, className, href }: Props) {
  const router = useRouter()
  const handleClick = () => {
    router.push(href)
  }
  return (
    <div
      className="cursor-pointer hover:shadow-md transition-shadow shadow-sm border py-3 px-4 bg-white grid grid-cols-10 gap-3 items-center rounded-md"
      onClick={handleClick}
    >
      <div
        className={`w-full col-start-1 col-end-3 border-r border-r-gray-200 pr-3 ${
          className ? className : ''
        }`}
      >
        {Icons[icon]}
      </div>
      <div className="w-full col-start-3 col-end-11">
        <h1 className="flex items-center text-2xl tracking-wide font-medium -mb-1">
          {title}
        </h1>
        <p className="text-4 text-gray-600 tracking-wide">{des}</p>
      </div>
    </div>
  )
}
