import Button from '~/components/Button'

export default function Top() {
  return (
    <div className="mt-5 grid xl:grid-cols-5 lg:gap-3">
      <div className="grid-cols-3 gap-3 col-start-1 col-span-3 hidden lg:grid">
        <Button bType="default" icon="github" className="text-gray-700">
          <span className="tracking-normal">Github</span>
        </Button>
        <Button bType="default" icon="twitter" className="text-blue-400">
          <span className="tracking-normal">Twitter</span>
        </Button>
        <Button bType="default" icon="email" className="text-gray-500">
          <span className="tracking-normal">Email</span>
        </Button>
      </div>
      <div className="lg:col-start-4 lg:col-end-6">
        <Button bType="primary" icon="right">
          <span className="tracking-normal">More about me</span>
        </Button>
      </div>
    </div>
  )
}
