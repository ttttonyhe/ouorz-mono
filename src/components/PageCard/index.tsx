import Icons from '~/components/Icons'
import { useRouter } from 'next/router'

interface Props {
  title: string
  des: string
  icon: string
  className?: string
  href?: string
}

export default function PageCard({ title, des, icon, className, href }: Props) {
  const router = useRouter()
  const handleClick = () => {
    if (href) {
      if (href.indexOf('http') === -1) {
        router.push(href)
      } else {
        window.location.href = href
      }
    }
  }
  return (
    <div
      className="cursor-pointer hover:shadow-md transition-shadow shadow-sm border py-3 px-4 bg-white flex items-center rounded-md"
      onClick={handleClick}
    >
      <div
        className={`w-20 h-auto border-r border-r-gray-200 pr-3 ${
          className ? className : ''
        }`}
      >
        {Icons[icon]}
      </div>
      <div className="w-full pl-3">
        <h1 className="flex items-center text-2xl tracking-wide font-medium -mb-1">
          {title}
        </h1>
        <p className="text-4 text-gray-600 tracking-wide">{des}</p>
      </div>
    </div>
  )
}
