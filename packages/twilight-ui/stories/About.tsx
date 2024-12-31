import React from 'react'

const About = () => {
	return (
		<div className="text-gray-600 dark:text-gray-100 justify-center flex items-center pt-20">
			<div className="tracking-wide leading-relaxed">
				<div className="flex gap-x-2 items-center">
					<img src="logo.png" className="h-[1.9rem] w-[1.9rem] shadow-md border rounded-full" alt="twilight toolkit logo" />
					<h1 className="text-3xl font-bold text-gray-700 dark:text-gray-50">
						Twilight Toolkit <span className="text-gray-400 dark:text-gray-300 font-normal">/</span>{' '}
						<span className="text-gray-500 dark:text-gray-200 text-base font-normal">UI</span>
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
