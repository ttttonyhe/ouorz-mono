import Icons from '~/components/Icons'
export default function CardEmpty() {
  return (
    <div className="w-full shadow-sm bg-white rounded-md border mb-6 text-center">
      <p className="text-xl tracking-wide text-gray-600 font-light p-5 flex justify-center">
        <span className="w-6 h-6 mr-3">{Icons.empty}</span>You Have Reached The
        Bottom Line
      </p>
    </div>
  )
}
