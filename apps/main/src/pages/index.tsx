import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Image from "next/image"
import Link from "next/link"
import React, { useState } from "react"
import PagesAndLinks from "~/components/Banners/PagesAndLinks"
import EmploymentCard from "~/components/Card/Employment"
import PaperCard from "~/components/Card/Paper"
import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import Top from "~/components/Top"
import { NextPageWithLayout } from "~/pages/_app"

const Home: NextPageWithLayout = () => {
	const [showPosts, setShowPosts] = useState(false)
	const [maskClass, setMaskClass] = useState("mask-x-r")

	return (
		<>
			<Head>
				<title>Tony (Lipeng) He</title>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="flex items-center justify-between gap-x-10 gap-y-8">
					<div className="hidden flex-shrink-0 pt-1 lg:block">
						<Image
							src="https://static.ouorz.com/avatar_real_small.jpg"
							height={131}
							width={131}
							alt="Tony He"
							className="rounded-md bg-gray-200 shadow-sm dark:border dark:border-gray-600"
						/>
					</div>
					<div className="flex flex-col gap-y-1">
						<h1 className="flex items-center whitespace-nowrap break-words text-3xl font-medium tracking-wide text-black dark:text-white lg:text-[1.8rem]">
							<span className="mr-2.5 inline-block animate-waveHand cursor-pointer hover:animate-waveHandAgain">
								👋
							</span>
							Hello, and welcome!
						</h1>
						<div className="flex flex-col gap-y-1.5 break-words px-1 text-justify text-4 font-light leading-relaxed tracking-wide text-gray-500 dark:text-gray-300 lg:text-2">
							<p>
								My name is Lipeng (Tony) He, and I am a student 👨‍🎓, software
								engineer 🧑‍💻, and researcher 🔬 with the{" "}
								<a
									href="https://uwaterloo.ca"
									target="_blank"
									className="inline-flex items-center gap-x-1 transition-colors hover:text-blue-500 dark:hover:text-blue-500">
									University of Waterloo
									<span className="flex h-5 w-5">
										<Icon name="externalLink" />
									</span>
								</a>
								.
							</p>
						</div>
					</div>
				</div>
			</section>
			<section className="mt-10">
				<div className="mt-6">
					<Top />
				</div>
			</section>
			<section className="mt-6">
				<div className="mt-5">
					<div className="mt-4">
						<PagesAndLinks />
					</div>
				</div>
			</section>
			<section className="mt-16">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="microscope" />
					</span>
					<span className="uppercase">Research Interests</span>
				</label>
				<div className="mt-[15px] flex flex-col gap-y-2 break-words px-0.5 text-justify text-3 font-light leading-relaxed tracking-wide text-gray-500 underline-offset-[6px] dark:text-gray-300 lg:text-[17px]">
					<p>
						<span>
							I am interested in both the theoretical & applied aspects of
							cryptography
						</span>
						<span>
							, especially its role across computing and data sciences.
						</span>{" "}
						My goal is{" "}
						<u className="decoration-gray-300">
							to enable society to gain the benefits of emerging technologies
							without sacrificing security & privacy
						</u>
						. And in the process, I hope to also unlock new application
						scenarios through a combination of systems design and cryptography.
					</p>
					<p>
						In my previous research experience, I worked on developing and
						analyzing secure systems and protocols that address issues related
						to:
					</p>
					<div className="flex flex-col items-center justify-between gap-y-2 pb-[12px] pr-1 pt-4.5 text-sm lg:flex-row">
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-800 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-500 text-white dark:bg-indigo-700">
								1
							</div>
							<div className="font-bold">Privacy-preserving computation</div>
						</div>
						<div className="hidden text-3 lg:block">and</div>
						<div className="text-normal flex w-full items-center gap-x-2 rounded-md border bg-white px-4 py-[7px] font-medium shadow-sm dark:border-gray-600 dark:bg-gray-800 lg:w-auto">
							<div className="flex h-5 w-5 items-center justify-center rounded-full bg-teal-500 text-white dark:bg-teal-700">
								2
							</div>
							<div>Software security and scalability</div>
						</div>
					</div>
					<p>
						My most recent research work has put an emphasis on Fully
						Homomorphic Encryption (FHE) and Privacy-preserving Machine Learning
						(PPML).
					</p>
					<p className="mt-5">My general objectives are to:</p>
					<ul className="mt-2 list-disc pl-5">
						<li className="pl-3">
							Design and develop systems and protocols that are provably secure,
							inexpensive and easy-to-use;
						</li>
						<li className="pl-3">
							Support the deployment of privacy-enhanced technology solutions in
							the real world for individuals and organizations to improve
							fairness and safety in the usage of data;
						</li>
						<li className="pl-3">
							Find new and innovative ways to apply cryptographic tools in
							society.
						</li>
					</ul>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-sm dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
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
						title="FedGLP: A Federated Prompt Learning Framework for Next-Generation Intelligent Manufacturing Systems"
						authors="Hao Pan, Xiaoli Zhao, Yuchen Jiang, Lipeng He, Bingquan Wang, and Yincan Shu"
						venue={{
							name: "IEEE Transactions on Industrial Informatics",
							href: "https://ieeexplore.ieee.org/xpl/RecentIssue.jsp?punumber=9424",
						}}
						accepted={false}
						links={[]}
					/>
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
						<span className="mr-1.5 flex h-5 w-5">
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
					<div
						onScroll={(e) => {
							const target = e.target as HTMLDivElement

							let maskClass = ""
							if (
								target.scrollLeft > 0 &&
								target.scrollLeft < target.scrollWidth - target.clientWidth
							) {
								maskClass = "mask-x-full"
							} else if (target.scrollLeft === 0) {
								maskClass = "mask-x-r"
							} else {
								maskClass = "mask-x-l"
							}

							setMaskClass(maskClass)
						}}
						className={`flex gap-x-4 overflow-x-auto whitespace-nowrap ${maskClass}`}>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
							organization="University of Waterloo"
							organizationFullName="CS 135 Designing Functional Programs"
							jobTitle="Instructional Support Assistant (ISA)"
							jobType="Teaching, Co-op"
							dateString="Aug 2024 - Present"
						/>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/zju_logo.png"
							organization="Zhejiang University"
							organizationFullName="ABC Lab, Institute of Cyberspace Research"
							jobTitle="Research Assistant"
							jobType="Research, Co-op"
							dateString="May - Aug 2024"
						/>
					</div>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/biorender_logo.png"
						organization="BioRender"
						organizationFullName="Science Suite Inc."
						jobTitle="Full Stack Software Engineer"
						jobType="SWE, Co-op"
						dateString="Jan - Apr 2023"
					/>
					<EmploymentCard
						organization="Safyre Labs Inc."
						jobTitle="Full Stack Software Engineer"
						jobType="SWE, Co-op"
						dateString="May -  Aug 2022"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/bitbuy_logo.png"
						organization="Bitbuy"
						organizationFullName="Bitbuy Technologies Inc."
						jobTitle="Front End Software Engineer"
						jobType="SWE, Co-op"
						dateString="Sep -  Dec 2021"
					/>
				</div>
			</section>
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
