import Header from '~/components/Header'
import Footer from '../Footer'

interface Props {
  children: React.ReactNode
}

export default function Content(props: Props) {
  const { children } = props
  return (
    <div>
      <Header></Header>
      <main className="w-full min-h-main xl:w-content h-auto mx-auto pt-0 xl:pt-20 px-5 xl:px-10">
        {children}
      </main>
      <Footer></Footer>
    </div>
  )
}
