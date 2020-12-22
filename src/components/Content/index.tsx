interface Props {
  children: React.ReactNode
}
export default function Content(props: Props) {
  const { children } = props
  return (
    <div className="w-full lg:w-content h-auto mx-auto pt-20 px-10">
      {children}
    </div>
  )
}
