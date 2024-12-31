import React from "react"

const About = () => {
	return (
		<div className="flex items-center justify-center pt-20 text-gray-600 dark:text-gray-100">
			<div className="leading-relaxed tracking-wide">
				<div className="flex items-center gap-x-2">
					<img
						src="logo.png"
						className="h-[1.9rem] w-[1.9rem] rounded-full border shadow-md"
						alt="twilight toolkit logo"
					/>
					<h1 className="text-3xl font-bold text-gray-700 dark:text-gray-50">
						Twilight Toolkit{" "}
						<span className="font-normal text-gray-400 dark:text-gray-300">
							/
						</span>{" "}
						<span className="text-base font-normal text-gray-500 dark:text-gray-200">
							UI
						</span>
					</h1>
				</div>
				<p className="pl-1">
					A super opinionated UI library for React <i>(for now)</i>
				</p>
			</div>
		</div>
	)
}

export default About
