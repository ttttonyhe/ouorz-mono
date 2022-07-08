import React from 'react'

const icons = {
	rss: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"
			/>
		</svg>
	),
	search: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
			/>
		</svg>
	),
	love: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
			/>
		</svg>
	),
	loveFill: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 20 20"
			fill="currentColor"
		>
			<path
				fillRule="evenodd"
				d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
				clipRule="evenodd"
			/>
		</svg>
	),
	chat: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"
			/>
		</svg>
	),
	twitter: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M15.3 5.55a2.9 2.9 0 0 0-2.9 2.847l-.028 1.575a.6.6 0 0 1-.68.583l-1.561-.212c-2.054-.28-4.022-1.226-5.91-2.799-.598 3.31.57 5.603 3.383 7.372l1.747 1.098a.6.6 0 0 1 .034.993L7.793 18.17c.947.059 1.846.017 2.592-.131 4.718-.942 7.855-4.492 7.855-10.348 0-.478-1.012-2.141-2.94-2.141zm-4.9 2.81a4.9 4.9 0 0 1 8.385-3.355c.711-.005 1.316.175 2.669-.645-.335 1.64-.5 2.352-1.214 3.331 0 7.642-4.697 11.358-9.463 12.309-3.268.652-8.02-.419-9.382-1.841.694-.054 3.514-.357 5.144-1.55C5.16 15.7-.329 12.47 3.278 3.786c1.693 1.977 3.41 3.323 5.15 4.037 1.158.475 1.442.465 1.973.538z"
				fill="currentColor"
			/>
		</svg>
	),
	email: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
			/>
		</svg>
	),
	github: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M12 2C6.475 2 2 6.475 2 12a9.994 9.994 0 0 0 6.838 9.488c.5.087.687-.213.687-.476 0-.237-.013-1.024-.013-1.862-2.512.463-3.162-.612-3.362-1.175-.113-.288-.6-1.175-1.025-1.413-.35-.187-.85-.65-.013-.662.788-.013 1.35.725 1.538 1.025.9 1.512 2.338 1.087 2.912.825.088-.65.35-1.087.638-1.337-2.225-.25-4.55-1.113-4.55-4.938 0-1.088.387-1.987 1.025-2.688-.1-.25-.45-1.275.1-2.65 0 0 .837-.262 2.75 1.026a9.28 9.28 0 0 1 2.5-.338c.85 0 1.7.112 2.5.337 1.912-1.3 2.75-1.024 2.75-1.024.55 1.375.2 2.4.1 2.65.637.7 1.025 1.587 1.025 2.687 0 3.838-2.337 4.688-4.562 4.938.362.312.675.912.675 1.85 0 1.337-.013 2.412-.013 2.75 0 .262.188.574.688.474A10.016 10.016 0 0 0 22 12c0-5.525-4.475-10-10-10z"
				fill="currentColor"
			/>
		</svg>
	),
	right: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M14 5l7 7m0 0l-7 7m7-7H3"
			/>
		</svg>
	),
	me: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
			/>
		</svg>
	),
	sticky: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"
			/>
		</svg>
	),
	cate: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"
			/>
		</svg>
	),
	preview: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"
			/>
		</svg>
	),
	empty: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
			/>
		</svg>
	),
	subscribe: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
			/>
		</svg>
	),
	left: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"
			/>
		</svg>
	),
	count: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
			/>
		</svg>
	),
	play: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
			/>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
			/>
		</svg>
	),
	pause: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
			/>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
			/>
		</svg>
	),
	pages: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"
			/>
		</svg>
	),
	comments: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
			/>
		</svg>
	),
	home: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
			/>
		</svg>
	),
	toc: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M4 6h16M4 12h16M4 18h7"
			/>
		</svg>
	),
	leftPlain: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M10 19l-7-7m0 0l7-7m-7 7h18"
			/>
		</svg>
	),
	mic: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				fill="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M12 13l6 9H6l6-9zm-1.06-2.44a1.5 1.5 0 1 1 2.12-2.12 1.5 1.5 0 0 1-2.12 2.12zM5.281 2.783l1.415 1.415a7.5 7.5 0 0 0 0 10.606l-1.415 1.415a9.5 9.5 0 0 1 0-13.436zm13.436 0a9.5 9.5 0 0 1 0 13.436l-1.415-1.415a7.5 7.5 0 0 0 0-10.606l1.415-1.415zM8.11 5.611l1.414 1.414a3.5 3.5 0 0 0 0 4.95l-1.414 1.414a5.5 5.5 0 0 1 0-7.778zm7.778 0a5.5 5.5 0 0 1 0 7.778l-1.414-1.414a3.5 3.5 0 0 0 0-4.95l1.414-1.414z"
			/>
		</svg>
	),
	githubLine: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				fill="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M5.883 18.653c-.3-.2-.558-.455-.86-.816a50.32 50.32 0 0 1-.466-.579c-.463-.575-.755-.84-1.057-.949a1 1 0 0 1 .676-1.883c.752.27 1.261.735 1.947 1.588-.094-.117.34.427.433.539.19.227.33.365.44.438.204.137.587.196 1.15.14.023-.382.094-.753.202-1.095C5.38 15.31 3.7 13.396 3.7 9.64c0-1.24.37-2.356 1.058-3.292-.218-.894-.185-1.975.302-3.192a1 1 0 0 1 .63-.582c.081-.024.127-.035.208-.047.803-.123 1.937.17 3.415 1.096A11.731 11.731 0 0 1 12 3.315c.912 0 1.818.104 2.684.308 1.477-.933 2.613-1.226 3.422-1.096.085.013.157.03.218.05a1 1 0 0 1 .616.58c.487 1.216.52 2.297.302 3.19.691.936 1.058 2.045 1.058 3.293 0 3.757-1.674 5.665-4.642 6.392.125.415.19.879.19 1.38a300.492 300.492 0 0 1-.012 2.716 1 1 0 0 1-.019 1.958c-1.139.228-1.983-.532-1.983-1.525l.002-.446.005-.705c.005-.708.007-1.338.007-1.998 0-.697-.183-1.152-.425-1.36-.661-.57-.326-1.655.54-1.752 2.967-.333 4.337-1.482 4.337-4.66 0-.955-.312-1.744-.913-2.404a1 1 0 0 1-.19-1.045c.166-.414.237-.957.096-1.614l-.01.003c-.491.139-1.11.44-1.858.949a1 1 0 0 1-.833.135A9.626 9.626 0 0 0 12 5.315c-.89 0-1.772.119-2.592.35a1 1 0 0 1-.83-.134c-.752-.507-1.374-.807-1.868-.947-.144.653-.073 1.194.092 1.607a1 1 0 0 1-.189 1.045C6.016 7.89 5.7 8.694 5.7 9.64c0 3.172 1.371 4.328 4.322 4.66.865.097 1.201 1.177.544 1.748-.192.168-.429.732-.429 1.364v3.15c0 .986-.835 1.725-1.96 1.528a1 1 0 0 1-.04-1.962v-.99c-.91.061-1.662-.088-2.254-.485z"
			/>
		</svg>
	),
	alipay: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				fill="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M18.408 16.79c-2.173-.95-3.72-1.646-4.64-2.086-1.4 1.696-2.872 2.72-5.08 2.72S5 16.064 5.176 14.392c.12-1.096.872-2.888 4.128-2.576 1.72.16 2.504.48 3.912.944.36-.664.664-1.4.888-2.176H7.88v-.616h3.072V8.864H7.2v-.68h3.752V6.592s.032-.248.312-.248H12.8v1.848h4v.68h-4v1.104h3.264a12.41 12.41 0 0 1-1.32 3.32c.51.182 2.097.676 4.76 1.483a8 8 0 1 0-1.096 2.012zM12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-3.568-5.632c1.44 0 2.824-.872 3.96-2.352-1.608-.776-2.944-1.16-4.44-1.16-1.304 0-1.984.8-2.104 1.416-.12.616.248 2.096 2.584 2.096z"
			/>
		</svg>
	),
	wxpay: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				fill="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M19.145 8.993l-9.799 5.608-.07.046a.646.646 0 0 1-.3.068.655.655 0 0 1-.58-.344l-.046-.092-1.83-3.95c-.024-.046-.024-.092-.024-.138 0-.184.139-.321.324-.321.07 0 .14.023.209.069l2.155 1.515c.162.092.348.161.556.161a.937.937 0 0 0 .348-.069l8.275-3.648C16.934 6.273 14.634 5.2 12 5.2c-4.42 0-7.9 3.022-7.9 6.6 0 1.366.5 2.673 1.432 3.781.048.057.12.137.214.235a4 4 0 0 1 1.101 3.102l-.025.297.716-.436a4 4 0 0 1 2.705-.536c.212.033.386.059.52.076.406.054.82.081 1.237.081 4.42 0 7.9-3.022 7.9-6.6 0-.996-.27-1.95-.755-2.807zM6.192 21.943a1 1 0 0 1-1.526-.932l.188-2.259a2 2 0 0 0-.55-1.551A6.993 6.993 0 0 1 4 16.868C2.806 15.447 2.1 13.695 2.1 11.8c0-4.75 4.432-8.6 9.9-8.6s9.9 3.85 9.9 8.6-4.432 8.6-9.9 8.6c-.51 0-1.01-.033-1.499-.098a23.61 23.61 0 0 1-.569-.084 2 2 0 0 0-1.353.268l-2.387 1.456z"
			/>
		</svg>
	),
	bitcoin: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			height="100%"
			width="100%"
		>
			<path fill="none" d="M0 0h24v24H0z" />
			<path
				fill="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-4H8V8h3V6h2v2h1a2.5 2.5 0 0 1 2 4 2.5 2.5 0 0 1-2 4h-1v2h-2v-2zm-1-3v1h4a.5.5 0 1 0 0-1h-4zm0-3v1h4a.5.5 0 1 0 0-1h-4z"
			/>
		</svg>
	),
	question: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
			/>
		</svg>
	),
	people: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
			/>
		</svg>
	),
	growth: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
			/>
		</svg>
	),
	microphone: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
			/>
		</svg>
	),
	sun: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
			/>
		</svg>
	),
	moon: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
			/>
		</svg>
	),
	lightBulb: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
			/>
		</svg>
	),
	star: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
			/>
		</svg>
	),
	eye: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
			/>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
			/>
		</svg>
	),
	global: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
			/>
		</svg>
	),
	users: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
			/>
		</svg>
	),
	userAdd: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
			/>
		</svg>
	),
	flag: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
			/>
		</svg>
	),
	thumbDown: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"
			/>
		</svg>
	),
	plane: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
			/>
		</svg>
	),
	ppt: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"
			/>
		</svg>
	),
	chatRounded: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
			/>
		</svg>
	),
	money: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="2"
				d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
			/>
		</svg>
	),
	checkCircle: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 20 20"
			fill="currentColor"
		>
			<path
				fillRule="evenodd"
				d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
				clipRule="evenodd"
			/>
		</svg>
	),
	warningCircle: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 20 20"
			fill="currentColor"
		>
			<path
				fillRule="evenodd"
				d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
				clipRule="evenodd"
			/>
		</svg>
	),
	reply: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			className="h-5 w-5"
			viewBox="0 0 20 20"
			fill="currentColor"
		>
			<path
				fillRule="evenodd"
				d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
				clipRule="evenodd"
			/>
		</svg>
	),
	arrowUp: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M5 15l7-7 7 7"
			/>
		</svg>
	),
	gear: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
			/>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
			/>
		</svg>
	),
	refresh: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
			/>
		</svg>
	),
	monitor: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
			/>
		</svg>
	),
	briefCase: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			viewBox="0 0 24 24"
			stroke="currentColor"
		>
			<path
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth={2}
				d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
			/>
		</svg>
	),
	eth: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			fill="none"
			preserveAspectRatio="xMidYMid meet"
			viewBox="0 0 24 24"
		>
			<rect x="0" y="0" width="24" height="24" fill="none" stroke="none" />
			<path
				fill="currentColor"
				d="m12 1.75l-6.25 10.5L12 16l6.25-3.75L12 1.75M5.75 13.5L12 22.25l6.25-8.75L12 17.25L5.75 13.5Z"
			/>
		</svg>
	),
	solana: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			stroke="currentColor"
			viewBox="0 0 508.07 390.17"
			className="scale-75 opacity-50"
		>
			<path
				d="M84.53,358.89A16.63,16.63,0,0,1,96.28,354H501.73a8.3,8.3,0,0,1,5.87,14.18l-80.09,80.09a16.61,16.61,0,0,1-11.75,4.86H10.31A8.31,8.31,0,0,1,4.43,439Z"
				transform="translate(-1.98 -55)"
			></path>
			<path
				d="M84.53,59.85A17.08,17.08,0,0,1,96.28,55H501.73a8.3,8.3,0,0,1,5.87,14.18l-80.09,80.09a16.61,16.61,0,0,1-11.75,4.86H10.31A8.31,8.31,0,0,1,4.43,140Z"
				transform="translate(-1.98 -55)"
			></path>
			<path
				d="M427.51,208.42a16.61,16.61,0,0,0-11.75-4.86H10.31a8.31,8.31,0,0,0-5.88,14.18l80.1,80.09a16.6,16.6,0,0,0,11.75,4.86H501.73a8.3,8.3,0,0,0,5.87-14.18Z"
				transform="translate(-1.98 -55)"
			></path>
		</svg>
	),
	openSea: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24"
			className="scale-90 opacity-50"
		>
			<path d="M12 0C5.374 0 0 5.374 0 12s5.374 12 12 12 12-5.374 12-12S18.629 0 12 0ZM5.92 12.403l.051-.081 3.123-4.884a.107.107 0 0 1 .187.014c.52 1.169.972 2.623.76 3.528-.088.372-.335.876-.614 1.342a2.405 2.405 0 0 1-.117.199.106.106 0 0 1-.09.045H6.013a.106.106 0 0 1-.091-.163zm13.914 1.68a.109.109 0 0 1-.065.101c-.243.103-1.07.485-1.414.962-.878 1.222-1.548 2.97-3.048 2.97H9.053a4.019 4.019 0 0 1-4.013-4.028v-.072c0-.058.048-.106.108-.106h3.485c.07 0 .12.063.115.132-.026.226.017.459.125.67.206.42.636.682 1.099.682h1.726v-1.347H9.99a.11.11 0 0 1-.089-.173l.063-.09c.16-.231.391-.586.621-.992.156-.274.308-.566.43-.86.024-.052.043-.107.065-.16.033-.094.067-.182.091-.269a4.57 4.57 0 0 0 .065-.223c.057-.25.081-.514.081-.787 0-.108-.004-.221-.014-.327-.005-.117-.02-.235-.034-.352a3.415 3.415 0 0 0-.048-.312 6.494 6.494 0 0 0-.098-.468l-.014-.06c-.03-.108-.056-.21-.09-.317a11.824 11.824 0 0 0-.328-.972 5.212 5.212 0 0 0-.142-.355c-.072-.178-.146-.339-.213-.49a3.564 3.564 0 0 1-.094-.197 4.658 4.658 0 0 0-.103-.213c-.024-.053-.053-.104-.072-.152l-.211-.388c-.029-.053.019-.118.077-.101l1.32.357h.01l.173.05.192.054.07.019v-.783c0-.379.302-.686.679-.686a.66.66 0 0 1 .477.202.69.69 0 0 1 .2.484V6.65l.141.039c.01.005.022.01.031.017.034.024.084.062.147.11.05.038.103.086.165.137a10.351 10.351 0 0 1 .574.504c.214.199.454.432.684.691.065.074.127.146.192.226.062.079.132.156.19.232.079.104.16.212.235.324.033.053.074.108.105.161.096.142.178.288.257.435.034.067.067.141.096.213.089.197.159.396.202.598a.65.65 0 0 1 .029.132v.01c.014.057.019.12.024.184a2.057 2.057 0 0 1-.106.874c-.031.084-.06.17-.098.254-.075.17-.161.343-.264.502-.034.06-.075.122-.113.182-.043.063-.089.123-.127.18a3.89 3.89 0 0 1-.173.221c-.053.072-.106.144-.166.209-.081.098-.16.19-.245.278-.048.058-.1.118-.156.17-.052.06-.108.113-.156.161-.084.084-.15.147-.208.202l-.137.122a.102.102 0 0 1-.072.03h-1.051v1.346h1.322c.295 0 .576-.104.804-.298.077-.067.415-.36.816-.802a.094.094 0 0 1 .05-.03l3.65-1.057a.108.108 0 0 1 .138.103z" />
		</svg>
	),
	magicEden: (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 541 541"
			className="opacity-50"
		>
			<image
				id="image0"
				width="541"
				height="541"
				x="0"
				y="0"
				href="data:image/png;base64,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"
			/>
		</svg>
	),
}

export default icons
