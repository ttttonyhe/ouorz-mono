import { Icon } from "@twilight-toolkit/ui"
import Head from "next/head"
import Image from "next/image"
import Link from "next/link"
import { useState } from "react"
import PagesAndLinks from "~/components/Banners/PagesAndLinks"
import EmploymentCard from "~/components/Card/Employment"
import PaperCard from "~/components/Card/Paper"
import ServiceCard from "~/components/Card/Service"
// import List from "~/components/List"
import { pageLayout } from "~/components/Page"
import SubscriptionBox from "~/components/SubscriptionBox"
import Top from "~/components/Top"
import type { NextPageWithLayout } from "~/pages/_app"

const Home: NextPageWithLayout = () => {
	// const [showPosts, setShowPosts] = useState(false)
	const [maskClass, setMaskClass] = useState("mask-x-r")

	return (
		<>
			<Head>
				<title>Tony (Lipeng) He</title>
			</Head>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="flex items-center justify-between gap-x-10 gap-y-8">
					<div className="-ml-1 flex flex-col gap-y-2.5">
						<h1 className="flex items-center whitespace-nowrap break-words font-medium text-3xl text-black tracking-wide lg:text-[1.8rem] dark:text-white">
							<span className="mr-2 inline-block animate-wave-hand cursor-pointer hover:animate-wave-hand-again">
								👋
							</span>
							Tony (Lipeng) He
						</h1>
						<div className="flex flex-col gap-y-1.5 break-words px-1 font-light text-4 text-gray-500 leading-relaxed tracking-wider lg:text-2 dark:text-gray-300">
							<p>
								I am a student, software engineer, and researcher at the{" "}
								<a
									href="https://uwaterloo.ca"
									target="_blank"
									className="inline-flex items-center gap-x-1 transition-colors hover:text-blue-500 dark:hover:text-blue-500"
									rel="noreferrer">
									University of Waterloo
									<span className="flex h-5 w-5">
										<Icon name="externalLink" />
									</span>
								</a>
								.
							</p>
						</div>
					</div>
					<div className="hidden shrink-0 pt-1 lg:block">
						<Image
							src="https://static.ouorz.com/avatar_real_small.jpg"
							height={105}
							width={105}
							alt="Tony teaching an undergraduate CS course"
							className="rounded-xl bg-gray-200 shadow-xs dark:border dark:border-gray-600"
						/>
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
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="me" />
					</span>
					<span className="uppercase">About Me</span>
				</label>
				<div className="mt-[15px] flex flex-col break-words px-1 text-justify font-light text-3 text-gray-500 leading-relaxed tracking-wide underline-offset-[6px] lg:text-left lg:text-[17px] dark:text-gray-300">
					<p>
						I&#39;m pursuing a Master of Mathematics (Research/Thesis) degree in
						Computer Science at UWaterloo. I am grateful to be advised by{" "}
						<a
							href="https://asokan.org/asokan/"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							N. Asokan
						</a>
						.
					</p>
					<p className="mt-3.5">
						I&#39;m part of{" "}
						<a
							href="https://ssg-research.github.io"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							Secure Systems Group (SSG)
						</a>
						,{" "}
						<a
							href="https://crysp.uwaterloo.ca"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							Cryptography, Security, and Privacy (CrySP) Lab
						</a>
						, and the{" "}
						<a
							href="https://uwaterloo.ca/cybersecurity-privacy-institute"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							Cybersecurity and Privacy Institute (CPI)
						</a>
						. I also worked with{" "}
						<a
							href="https://jianliu.phd"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							Jian Liu
						</a>{" "}
						at{" "}
						<a
							href="https://zju-abc.com"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							ABC Lab
						</a>
						,{" "}
						<a
							href="https://www.zju.edu.cn/english"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							Zhejiang University
						</a>
						. Currently, my office is located in the{" "}
						<a
							href="https://cs.uwaterloo.ca/about/visit-us"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							William G. Davis Computer Research Centre
						</a>
						, DC 3333B, M3.
					</p>
					<p className="mt-3.5">
						I&#39;m in pursuit of knowledge, experience, and the various other
						beautiful things life has to offer. I strive to{" "}
						<span className="inline-block bg-gradient-to-r from-blue-500 via-green-500 to-indigo-500 bg-clip-text text-transparent">
							live deliberately
						</span>
						. Before research, I spent some years doing software engineering. In
						the limit of my life, I also hope to be a pianist,{" "}
						<a
							href="https://lists.lipeng.ac/subscription/form"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							wri
						</a>
						<a
							href="https://www.instagram.com/endings_be_damned"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							ter
						</a>
						,{" "}
						<a
							href="https://kukfm.com"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							podcaster
						</a>
						, designer, and{" "}
						<a
							href="https://www.linkedin.com/in/~lhe"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							entrepreneur
						</a>
						.
					</p>
				</div>
			</section>
			<section className="mt-16">
				<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="microscope" />
					</span>
					<span className="uppercase">Research Interests</span>
				</label>
				<div className="mt-[15px] flex flex-col gap-y-2 break-words px-1 text-justify font-light text-3 text-gray-500 leading-relaxed tracking-wide underline-offset-[6px] lg:text-[17px] dark:text-gray-300">
					<p>
						My research interests span <strong>computer security</strong> and
						the <strong>theory & applications of cryptography</strong>{" "}
						(especially across computing and data sciences).
					</p>
					<p className="mt-3.5">
						I think broadly about the privacy, security and trustworthiness of
						modern computing systems; this intersects with areas such as:
					</p>
					<ul className="my-2 list-disc pl-5">
						<li className="pl-3">
							Trustworthy Machine Learning (ML Safety, Security & Privacy)
						</li>
						<li className="pl-3">Blockchain Security and Scalability, and</li>
						<li className="pl-3">Secure Computation</li>
					</ul>
					<p className="mt-3.5">
						Through a combination of systems design and analysis, I hope to make
						deployed solutions more reliable, useful, and aligned, while also
						enabling entirely new application scenarios.
					</p>
					<hr className="mt-3.5 mb-3.5 dark:border-gray-700" />
					<p>
						My research is supported by the International Master&#39;s Award of
						Excellence (IMAE) and the David R. Cheriton Graduate Scholarship.
					</p>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="newspaper" />
						</span>
						<span className="hidden uppercase lg:block">
							Selected Publications
						</span>
						<span className="block uppercase lg:hidden">Publications</span>
					</label>
					<span className="flex items-center gap-x-1 text-right text-gray-500 underline-offset-4 dark:text-gray-400">
						* indicates equal contribution
					</span>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="Activation Approximations Can Incur Safety Vulnerabilities Even in Aligned LLMs: Comprehensive Analysis and Defense"
						authors="Jiawen Zhang*, Kejia Chen*, Lipeng He*, Jian Lou, Dan Li, Zunlei Feng, Mingli Song, Jian Liu, Kui Ren, and Xiaohu Yang"
						accepted={true}
						venue={{
							name: "USENIX Security 2025",
							href: "https://www.usenix.org/conference/usenixsecurity25",
							color: "border-l-red-500! border-l-4",
						}}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2502.00840",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="On the Atomicity and Efficiency of Blockchain Payment Channels"
						authors="Di Wu, Shoupeng Ren, Yuman Bai, Lipeng He, Jian Liu, Wu Wen, Kui Ren, and Chun Chen"
						venue={{
							name: "USENIX Security 2025",
							href: "https://www.usenix.org/conference/usenixsecurity25",
							color: "border-l-red-500! border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Paper",
								href: "https://eprint.iacr.org/2025/180",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="LookAhead: Preventing DeFi Attacks via Unveiling Adversarial Contracts"
						authors="Shoupeng Ren, Lipeng He, Tianyu Tu, Di Wu, Jian Liu, Kui Ren, and Chun Chen"
						venue={{
							name: "FSE 2025",
							href: "https://conf.researchr.org/home/fse-2025",
							color: "border-l-red-500! border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2401.07261",
								default: true,
							},
							{
								label: "Code",
								href: "https://github.com/zju-abclab/LookAhead",
							},
						]}
					/>
					<PaperCard
						title="Secure Transformer Inference Made Non-interactive"
						authors="Jiawen Zhang, Xinpeng Yang, Lipeng He, Kejia Chen, Wen-jie Lu, Yinghao Wang, Xiaoyang Hou, Jian Liu, Kui Ren and Xiaohu Yang"
						venue={{
							name: "NDSS 2025",
							href: "https://www.ndss-symposium.org/ndss2025/",
							color: "border-l-red-500! border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Paper",
								href: "https://eprint.iacr.org/2024/136",
								default: true,
							},
							{
								label: "Code",
								href: "https://github.com/zju-abclab/NEXUS",
							},
						]}
					/>
				</div>
				<hr className="mt-5 dark:border-gray-700" />
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="FedVLP: Visual-aware Latent Prompt Generation for Multimodal Federated Learning"
						authors="Hao Pan, Xiaoli Zhao, Yuchen Jiang, Lipeng He, Bingquan Wang, and Yincan Shu"
						accepted={true}
						venue={{
							name: "Computer Vision and Image Understanding",
							href: "https://www.sciencedirect.com/journal/computer-vision-and-image-understanding",
							color: "border-l-yellow-400! border-l-4",
						}}
						links={[
							{
								label: "Paper",
								href: "https://www.sciencedirect.com/science/article/abs/pii/S1077314225001651",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="A Survey of Multimodal Federated Learning: Background, Applications, and Perspectives"
						authors="Hao Pan, Xiaoli Zhao, Lipeng He, Yicong Shi and Xiaogang Lin"
						venue={{
							name: "Multimedia Systems",
							href: "https://link.springer.com/journal/530",
							color: "border-l-green-600! border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Paper",
								href: "https://link.springer.com/article/10.1007/s00530-024-01422-9",
								default: true,
							},
							{
								label: "Code",
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
							color: "border-l-gray-500! border-l-4",
						}}
						accepted={true}
						links={[
							{
								label: "Paper",
								href: "https://www.atlantis-press.com/proceedings/deca-23/125994999",
								default: true,
							},
						]}
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="article" />
						</span>
						<span className="block uppercase">Preprints</span>
					</label>
					<Link
						href="https://scholar.google.com/citations?user=6yFlE_sAAAAJ"
						target="_blank"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						Citations
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="Safety at One Shot: Patching Fine-Tuned LLMs with A Single Instance"
						authors="Jiawen Zhang, Lipeng He, Kejia Chen, Jian Lou, Jian Liu, Xiaohu Yang, and Ruoxi Jia"
						accepted={false}
						links={[]}
					/>
					<PaperCard
						title="StructEval: Benchmarking LLMs' Capabilities to Generate Structural Outputs"
						authors="Jialin Yang, Dongfu Jiang, Lipeng He, Sherman Siu, Yuxuan Zhang, Disen Liao, Benjamin Schneider, Ping Nie, Wenhu Chen, et al."
						accepted={false}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2505.20139",
								default: true,
							},
							{
								label: "Code",
								href: "https://github.com/TIGER-AI-Lab/StructEval",
							},
							{
								label: "Website",
								href: "https://tiger-ai-lab.github.io/StructEval",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="Token-by-Token Manipulation: Inference-Time Jailbreaking on Production LLMs via Autoregressive Harmful Guidance"
						authors="Jiawen Zhang*, Lipeng He*, Kejia Chen*, Jian Liu, Zunlei Feng, Mingli Song, Jian Lou, Dan Li, and Xiaohu Yang"
						accepted={false}
						links={[]}
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="personSpeaks" />
						</span>
						<span className="uppercase">Talks</span>
					</label>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="UWaterloo Cybersecurity and Privacy Institute (CPI) Graduate Student Conference (GradConf 2025)"
						authors="Activation Approximations Can Incur Safety Vulnerabilities Even in Aligned LLMs: Comprehensive Analysis and Defense"
						accepted={true}
						venue={{
							name: "Spotlight Talk",
							href: "https://uwaterloo.ca/cybersecurity-privacy-institute/gradstudentconference",
							color: "border-l-gray-500! border-l-4",
						}}
						links={[
							{
								label: "Poster",
								href: "https://uwaterloo.ca/cybersecurity-privacy-institute/sites/default/files/uploads/documents/poster-84.pdf",
								default: true,
							},
							{
								label: "Slides",
								href: "https://static.ouorz.com/quada_gradconf_slides.pdf",
							},
						]}
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="service" />
						</span>
						<span className="uppercase">Academic Services</span>
					</label>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<ServiceCard
						serviceRole="Program Committee Member"
						serviceType="Conference"
						serviceTitle="Privacy Enhancing Technologies Symposium (PoPETs/PETS) 2026"
						serviceOrganization="Artifact Evaluation"
					/>
					<ServiceCard
						serviceRole="Program Committee Member"
						serviceType="Conference"
						serviceTitle="ACM Conference on Computer and Communications Security (CCS) 2025"
						serviceOrganization="Artifact Evaluation"
					/>
					<ServiceCard
						serviceRole="Invited Reviewer"
						serviceType="Journal"
						serviceTitle="IEEE Transactions on Dependable and Secure Computing (TDSC)"
					/>
					<ServiceCard
						serviceRole="Student Member"
						serviceType="Membership"
						serviceTitle="Association for Computing Machinery (ACM)"
						serviceOrganization="lipenghe@acm.org"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="suitcase" />
						</span>
						<span className="uppercase">Experience</span>
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
						jobTitle="Instructional Apprentice (IA)"
						jobType="Teaching"
						dateString="Sept 2025 - Present"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/ezra_logo.jpg"
						organization="Bluelet AI"
						organizationFullName="Agentic AI and data platform solutions for talent acquisition and matching"
						jobTitle="Interim CTO"
						jobType="Leadership"
						dateString="May 2025 - June 2025"
					/>
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
							orgLogoSrc="https://static.ouorz.com/crysp_logo.png"
							organization="University of Waterloo"
							organizationFullName="Cryptography, Security, and Privacy (CrySP) Lab"
							jobTitle="Research Assistant (URA)"
							jobType="Research, Part-time"
							dateString="Jan 2025 - Present"
						/>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
							organization="University of Waterloo"
							organizationFullName="CS 135 Designing Functional Programs"
							jobTitle="Teaching Assistant (ISA)"
							jobType="Teaching, Co-op"
							dateString="Aug 2024 - Dec 2024"
						/>
					</div>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/zju_logo.png"
						organization="Zhejiang University"
						organizationFullName="ABC Lab, Institute of Cyberspace Research"
						jobTitle="Research Assistant"
						jobType="Research, Co-op"
						dateString="May - Aug 2024"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/biorender_logo.png"
						organization="BioRender"
						organizationFullName="SaaS, Y Combinator W18"
						organizationLocation="Toronto, ON"
						jobTitle="Full Stack Software Engineer"
						jobType="SWE, Co-op"
						dateString="Jan - Apr 2023"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/jewlr-logo.svg"
						organization="Safyre Labs"
						organizationFullName="E-Commerce Platform, Supply Chain"
						jobTitle="Full Stack Software Engineer"
						jobType="SWE, Co-op"
						dateString="May -  Aug 2022"
						organizationLocation="North York, ON"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/bitbuy_logo.png"
						organization="Bitbuy"
						organizationFullName="Cryptocurrency Exchange, Publicly Traded on TSX: WNDR"
						jobTitle="Software Engineer"
						jobType="SWE, Co-op"
						dateString="Sep -  Dec 2021"
						organizationLocation="Toronto, ON"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="graduationCapOutline" />
						</span>
						<span className="uppercase">Education</span>
					</label>
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
							organizationFullName="Computer Science"
							jobTitle="Master's Degree (Research/Thesis)"
							jobType="MPhil"
							dateString="Sep 2025 - Present"
						/>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
							organization="University of Waterloo"
							organizationFullName="Mathematics (Minor in Computing)"
							jobTitle="Honours Bachelor's Degree (Co-op)"
							jobType="Undergrad"
							dateString="Sep 2020 - Apr 2025"
						/>
					</div>
				</div>
			</section>
			<section className="mt-14 mb-24">
				<div className="flex justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="edit" />
						</span>
						<span className="uppercase">Newsletter</span>
					</label>
					<Link
						href="https://kukfm.com"
						target="_blank"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						Podcast
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
					{/* <button
						data-cy="showIndexPosts"
						onClick={() => setShowPosts(!showPosts)}
						aria-label="Toggle between posts and subscription box"
						className="effect-pressing inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs hover:shadow-inner dark:border-gray-600 dark:bg-transparent dark:text-gray-500 dark:hover:bg-gray-800">
						<span
							className={`flex h-5 w-5 duration-200 ${showPosts ? "rotate-180" : "rotate-0"}`}>
							<Icon name="arrowUp" />
						</span>
					</button> */}
				</div>
				{/* {showPosts ? (
					<div className="mt-5 animate-appear">
						<List type="index" />
					</div>
				) : ( */}
				<div className="mt-5">
					<SubscriptionBox type="sm" />
				</div>
				{/* )} */}
			</section>
		</>
	)
}

Home.layout = pageLayout

export default Home
