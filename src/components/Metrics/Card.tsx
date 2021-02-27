import Icons from '~/components/Icons'

interface PropsType {
  footer: string
  value: string
  link: string
  icon: string
  colorHex: string
  subValue?: string
}

export default function MetricCard({
  footer,
  value,
  link,
  icon,
  colorHex,
  subValue,
}: PropsType) {
  return (
    <div
      onClick={() => navigateTo(link)}
      className="rounded-md border shadow-sm hover:shadow-md py-4 px-5 bg-white cursor-pointer"
      style={{ borderBottom: `5px solid ${colorHex}` }}
    >
      <h1
        className={`font-bold text-3.5xl tracking-wide flex items-center -mb-1 ${
          !value && 'animate-pulse'
        }`}
      >
        <span>
          {value || '- - -'}
          {subValue && '/' + subValue}
        </span>
        {value && <span className="w-7 h-7 ml-1 mt-1">{Icons[icon]}</span>}
      </h1>
      <p className="text-gray-500 tracking-wide overflow-hidden overflow-ellipsis whitespace-nowrap">
        {footer} →
      </p>
    </div>
  )
}

const navigateTo = (link) => {
  window.open(link)
}
