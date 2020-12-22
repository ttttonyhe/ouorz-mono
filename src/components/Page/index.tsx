import Header from '~/components/Header'

interface Props {
  children: React.ReactNode
}

export default function Page(props: Props) {
  const { children } = props
  return (
    <div>
      <Header></Header>
      {children}
    </div>
  )
}
