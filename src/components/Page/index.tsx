import Header from '~/components/Header'
import Footer from '../Footer'

interface Props {
  children: React.ReactNode
}

export default function Page(props: Props) {
  const { children } = props
  return (
    <div>
      <Header></Header>
      <main className="w-full min-h-screen lg:w-page h-auto mx-auto pt-20">
        {children}
      </main>
      <Footer></Footer>
    </div>
  )
}
