import ContentLoader from "react-content-loader"

export default function CardSkeleton() {
	return (
		<div className="mb-6 w-full rounded-md border bg-white p-10 text-center shadow-xs dark:border-gray-800 dark:bg-gray-800">
			<ContentLoader
				className="block dark:hidden"
				uniqueKey="card-skeleton-light"
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={100}
				backgroundColor="#f3f3f3"
				foregroundColor="#ecebeb">
				<rect x="0" y="0" rx="5" ry="5" width="31%" height="100" />
				<rect x="34%" y="0" rx="5" ry="5" width="66%" height="30" />
				<rect x="34%" y="41" rx="2" ry="2" width="60%" height="15" />
				<rect x="34%" y="63" rx="2" ry="2" width="50%" height="15" />
				<rect x="34%" y="85" rx="2" ry="2" width="55%" height="15" />
			</ContentLoader>
			<ContentLoader
				className="hidden dark:block"
				uniqueKey="card-skeleton-dark"
				speed={2}
				width={100}
				style={{ width: "100%" }}
				height={100}
				backgroundColor="#525252"
				foregroundColor="#737373">
				<rect x="0" y="0" rx="5" ry="5" width="31%" height="100" />
				<rect x="34%" y="0" rx="5" ry="5" width="66%" height="30" />
				<rect x="34%" y="41" rx="2" ry="2" width="60%" height="15" />
				<rect x="34%" y="63" rx="2" ry="2" width="50%" height="15" />
				<rect x="34%" y="85" rx="2" ry="2" width="55%" height="15" />
			</ContentLoader>
		</div>
	)
}
