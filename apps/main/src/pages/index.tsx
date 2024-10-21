import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Link from "next/link"
import React, { useState } from "react"
import PagesAndLinks from "~/components/Banners/PagesAndLinks"
import ResearchPapers from "~/components/Banners/Research"
import EmploymentCard from "~/components/Card/Employment"
import PaperCard from "~/components/Card/Paper"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import Top from "~/components/Top"
import { NextPageWithLayout } from "~/pages/_app"

const Emphasis = ({
	name,
	className,
	children,
}: {
	name: string
	className?: string
	children?: React.ReactNode
}) => (
	<span
		className={`${className || ""} inline-flex items-center gap-x-2 rounded-md border border-gray-300 bg-white px-[8px] py-0.5 text-sm font-normal tracking-normal dark:border-gray-600 dark:bg-gray-700 lg:py-1`}>
		{children ? (
			<>
				<span className="border-r border-gray-300 pr-2 dark:border-gray-600">
					{name}
				</span>
				<span>{children}</span>
			</>
		) : (
			<span>{name}</span>
		)}
	</span>
)

const Home: NextPageWithLayout = () => {
	const [showPosts, setShowPosts] = useState(false)

	return (
		<>
			<Head>
				<title>Tony (Lipeng) He</title>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div>
					<h1 className="mb-1.5 flex items-center whitespace-nowrap break-words text-3xl font-medium leading-relaxed tracking-wide text-black dark:text-white lg:text-1">
						<span className="mr-2.5 inline-block animate-waveHand cursor-pointer hover:animate-waveHandAgain">
							👋
						</span>
						Hello, and welcome!
					</h1>
					<div className="flex flex-col gap-y-1.5 break-words px-1 pb-1.5 pt-1 text-justify text-3 font-light leading-relaxed tracking-wide text-gray-500 dark:text-gray-300 lg:text-left lg:text-2">
						<p>
							My name is Lipeng He
							<span className="hidden lg:inline"> </span>
							<Emphasis
								name="Preferred First Name"
								className="hidden -translate-y-0.5 lg:inline-flex">
								<b>Tony</b>
							</Emphasis>
							, and I am currently a student 👨‍🎓 and researcher 🔬 with the{" "}
							<Emphasis
								name="University of Waterloo"
								className="hidden translate-y-[1px] border-l-4 !border-l-yellow-300 lg:inline-flex">
								<Link
									href="https://uwaterloo.ca"
									target="_blank"
									aria-label="Visit University of Waterloo's website"
									className="transition-colors hover:text-blue-500">
									<span className="flex h-4.5 w-4.5">
										<Icon name="externalLink" />
									</span>
								</Link>
							</Emphasis>
							<span className="inline lg:hidden">University of Waterloo</span> .
						</p>
						<p>
							I was previously a{" "}
							<Emphasis
								name="Full Stack Software Engineer"
								className="hidden border-l-4 !border-l-blue-400 lg:inline-flex">
								Intern
							</Emphasis>
							<span className="lg:hidden">Full Stack Software Engineer</span> at
							various technology startups based in Toronto, Canada 🇨🇦.
						</p>
					</div>
				</div>
			</section>
			<section className="mt-12">
				{/* <label className="border-gray-301 inline-flex items-center rounded-full border bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-2.5 flex h-5 w-5 text-green-500">
						<Icon name="plane" />
					</span>
					<span className="uppercase">How to Reach Me</span>
				</label> */}
				<div className="mt-6">
					<Top />
				</div>
			</section>
			<section className="mt-6">
				{/* <label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-yellow-400">
						<Icon name="flag" />
					</span>
					<span className="uppercase">Featured Content</span>
				</label> */}
				<div className="mt-5">
					{/* <ResearchPapers /> */}
					<div className="mt-4">
						<PagesAndLinks />
					</div>
				</div>
			</section>
			<section className="mt-14">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5 text-purple-500">
						<Icon name="microscope" />
					</span>
					<span className="uppercase">Research Interests</span>
				</label>
				<div className="mt-[15px] flex flex-col gap-y-2 break-words px-0.5 text-justify text-3 font-light leading-relaxed tracking-wide text-gray-500 underline-offset-[6px] dark:text-gray-300 lg:text-[17px]">
					<p>
						<span>
							I am interested in both the{" "}
							<u className="decoration-gray-300">
								Theoretical & Applied Aspects of Cryptography
							</u>
						</span>
						<span>
							{" "}
							and their applications throughout and beyond computing & data
							sciences.
						</span>
					</p>
					<p>
						In my previous research experience, I worked on developing and
						analyzing{" "}
						<u className="decoration-gray-300">
							Cryptographic Systems and Protocols
						</u>{" "}
						that address issues related to:
					</p>
					<div className="flex flex-col items-center justify-between gap-y-2 pb-[12px] pr-1 pt-4.5 text-sm lg:flex-row">
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-800 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-purple-500 text-white dark:bg-purple-700">
								1
							</div>
							<div className="font-bold">Privacy-preserving computation</div>
						</div>
						<div className="hidden text-3 lg:block">and</div>
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-800 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-purple-500 text-white dark:bg-purple-700">
								2
							</div>
							<div>Software security and usability</div>
						</div>
					</div>
					<p>
						My most recent research work has put an emphasis on Fully
						Homomorphic Encryption (FHE) and Privacy-preserving Machine Learning
						(PPML).
					</p>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-blue-500">
							<Icon name="article" />
						</span>
						<span className="hidden uppercase lg:block">
							Selected Publications
						</span>
						<span className="block uppercase lg:hidden">Publications</span>
					</label>
					<Link
						href="https://scholar.google.com/citations?user=6yFlE_sAAAAJ"
						target="_blank"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						Google Scholar
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="LookAhead: Preventing DeFi Attacks via Unveiling Adversarial Contracts"
						authors="Shoupeng Ren, Lipeng He, Tianyu Tu, Di Wu, Jian Liu, Kui Ren, and Chun Chen"
						venue={{
							name: "FSE 2025",
							href: "https://conf.researchr.org/home/fse-2025",
						}}
						accepted={false}
						links={[
							{
								label: "arXiv ePrint",
								href: "https://arxiv.org/abs/2401.07261",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="Secure Transformer Inference Made Non-interactive"
						authors="Jiawen Zhang, Xinpeng Yang, Lipeng He, Kejia Chen, Wen-jie Lu, Yinghao Wang, Xiaoyang Hou, Jian Liu, Kui Ren and Xiaohu Yang"
						venue={{
							name: "NDSS 2025",
							href: "https://www.ndss-symposium.org/ndss2025/",
							color: "!border-l-red-500 border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Cryptology ePrint",
								href: "https://eprint.iacr.org/2024/136",
								default: true,
							},
							{
								label: "Github",
								href: "https://github.com/zju-abclab/NEXUS",
							},
						]}
					/>
					<PaperCard
						title="A Survey of Multimodal Federated Learning: Background, Applications, and Perspectives"
						authors="Hao Pan, Xiaoli Zhao, Lipeng He, Yicong Shi and Xiaogang Lin"
						venue={{
							name: "Multimedia Systems",
							href: "https://link.springer.com/journal/530",
							color: "!border-l-green-600 border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Volume 30, Article 222",
								href: "https://link.springer.com/article/10.1007/s00530-024-01422-9",
								default: true,
							},
							{
								label: "Github",
								href: "https://github.com/haopr/MMFL",
							},
						]}
					/>
					<PaperCard
						title="A Comparative Examination of Network and Contract-Based Blockchain Storage Solutions for Decentralized Applications"
						authors="Lipeng He"
						venue={{
							name: "DECA 2023",
							href: "https://www.atlantis-press.com/proceedings/deca-23",
							color: "!border-l-gray-500 border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Atlantis Highlights in Computer Sciences",
								href: "https://www.atlantis-press.com/proceedings/deca-23/125994999",
								default: true,
							},
						]}
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-teal-500">
							<Icon name="suitcase" />
						</span>
						<span className="uppercase">Employment</span>
					</label>
					<Link
						href="https://www.linkedin.com/in/~lhe/"
						target="_blank"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						LinkedIn
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
						organization="University of Waterloo"
						organizationFullName="CS 135 Designing Functional Programs"
						jobTitle="Instructional Support Assistant (ISA)"
						jobType="Full-time Internship"
						dateString="Aug 2024 - Present"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/zju_logo.png"
						organization="Zhejiang University"
						organizationFullName="ABC Lab, Institute of Cyberspace Research"
						jobTitle="Research Assistant"
						jobType="Full-time Internship"
						dateString="May - Aug 2024"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/biorender_logo.png"
						organization="BioRender"
						organizationFullName="Science Suite Inc."
						jobTitle="Full Stack Software Engineer"
						jobType="Full-time Internship"
						dateString="Jan - Apr 2023"
					/>
					<EmploymentCard
						organization="Safyre Labs Inc."
						jobTitle="Full Stack Software Engineer"
						jobType="Full-time Internship"
						dateString="May -  Aug 2022"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/bitbuy_logo.png"
						organization="Bitbuy"
						organizationFullName="Bitbuy Technologies Inc."
						jobTitle="Front End Software Engineer"
						jobType="Full-time Internship"
						dateString="Sep -  Dec 2021"
					/>
				</div>
			</section>
			{/* <section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5 text-rose-500">
							<Icon name="suitcase" />
						</span>
						<span className="uppercase">Education</span>
					</label>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
						organization="University of Waterloo"
						organizationFullName="Bachelor of Mathematics, Honours, Co-operative Program (Minor in Computing)"
						jobTitle="Mathematics, Combinatorics & Optimization"
						jobType="Undergraduate"
						dateString="Sep 2020 - Apr 2025"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/ntu_logo.webp"
						organization="Nanyang Technological University"
						organizationFullName="GEM Trailblazer Exchange Program"
						jobTitle="Mathematical Sciences"
						jobType="Exchange"
						dateString="Aug - Dec 2023"
					/>
				</div>
			</section> */}
			<section className="mb-24 mt-14">
				<div className="flex justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="edit" />
						</span>
						<span className="uppercase">Blog Posts</span>
					</label>
					<button
						data-cy="showIndexPosts"
						onClick={() => setShowPosts(!showPosts)}
						aria-label="Toggle between posts and subscription box"
						className="effect-pressing inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm hover:shadow-inner dark:border-gray-600 dark:bg-transparent dark:text-gray-500 dark:hover:bg-gray-800">
						<span
							className={`flex h-5 w-5 duration-200 ${showPosts ? "rotate-180" : "rotate-0"}`}>
							<Icon name="arrowUp" />
						</span>
					</button>
				</div>
				{showPosts ? (
					<div className="mt-5 animate-appear">
						<List type="index" />
					</div>
				) : (
					<div className="mt-5">
						<SubscriptionBox type="sm" />
					</div>
				)}
			</section>
		</>
	)
}

Home.layout = pageLayout

export default Home
