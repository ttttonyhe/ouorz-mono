import { Icon } from "@twilight-toolkit/ui"
import Link from "next/link"
import { useState } from "react"
import PagesAndLinks from "~/components/Banners/PagesAndLinks"
import EmploymentCard from "~/components/Card/Employment"
import PaperCard from "~/components/Card/Paper"
import ServiceCard from "~/components/Card/Service"
import { pageLayout } from "~/components/Page"
import ProfilePicture from "~/components/ProfilePicture"
import SubscriptionBox from "~/components/SubscriptionBox"
import Top from "~/components/Top"
import {
	OTHER_PUBLICATIONS,
	PREPRINTS,
	PRIMARY_PUBLICATIONS,
	TEACHING_GROUPS,
	type PublicationItem,
} from "~/content/profile"
import type { NextPageWithLayout } from "~/pages/_app"

const renderPaper = (paper: PublicationItem) =>
	paper.accepted && paper.venue ? (
		<PaperCard
			key={paper.title}
			title={paper.title}
			authors={paper.authors}
			accepted={true}
			venue={paper.venue}
			links={paper.links}
		/>
	) : (
		<PaperCard
			key={paper.title}
			title={paper.title}
			authors={paper.authors}
			accepted={false}
			links={paper.links}
		/>
	)

const Home: NextPageWithLayout = () => {
	const [maskClass, setMaskClass] = useState("mask-x-r")

	return (
		<>
			<section className="mt-0 pt-24 lg:mt-20 lg:pt-0">
				<div className="flex items-center justify-between gap-x-10 gap-y-8">
					<div className="flex flex-col gap-y-2.5">
						<h1 className="flex items-center text-3xl font-medium tracking-wide wrap-break-word whitespace-nowrap text-black lg:text-[1.8rem] dark:text-white">
							Tony (Lipeng) He
						</h1>
						<div className="flex flex-col gap-y-1.5 px-1 text-4 leading-relaxed font-light tracking-wider wrap-break-word text-gray-500 lg:text-2 dark:text-gray-300">
							<p>
								Any improvements made anywhere besides the bottleneck are an
								illusion. <i>— Theory of Constraints</i>
							</p>
						</div>
					</div>
					<ProfilePicture className="mt-1 hidden shrink-0 lg:block" />
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
				<h2
					id="about"
					className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="me" />
					</span>
					<span className="uppercase">About</span>
				</h2>
				<div className="mt-3.75 flex flex-col px-1 text-justify text-3 leading-relaxed font-light tracking-wide wrap-break-word text-gray-500 underline-offset-[6px] lg:text-left lg:text-[17px] dark:text-gray-300">
					<p>
						I&#39;m a computer science PhD student at the University of
						Waterloo; part of{" "}
						<a
							href="https://ssg-research.github.io"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Secure Systems Group (SSG)
						</a>
						,{" "}
						<a
							href="https://crysp.uwaterloo.ca"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Cryptography, Security, and Privacy (CrySP) Lab
						</a>
						, and the{" "}
						<a
							href="https://uwaterloo.ca/cybersecurity-privacy-institute"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Cybersecurity and Privacy Institute (CPI)
						</a>
						.
					</p>
					<p className="mt-3.5">
						I&#39;m grateful to be advised by{" "}
						<a
							href="https://asokan.org/asokan/"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							N. Asokan
						</a>{" "}
						and{" "}
						<a
							href="https://cs.uwaterloo.ca/~y328yu/"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Yaoliang Yu
						</a>
						. I also worked with{" "}
						<a
							href="https://jianliu.phd"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Jian Liu
						</a>{" "}
						at{" "}
						<a
							href="https://www.zju.edu.cn/english"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Zhejiang University
						</a>{" "}
						and did applied cryptography research at{" "}
						<a
							href="https://zju-abc.com"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							ABC Lab
						</a>
						. Currently, my office is located in the{" "}
						<a
							href="https://cs.uwaterloo.ca/about/visit-us"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							William G. Davis Computer Research Centre
						</a>
						, DC 3333.
					</p>
					<p className="mt-3.5">
						I&#39;m in pursuit of knowledge, experience, and the various other
						beautiful things life has to offer. I strive to{" "}
						<a
							href="https://www.goodreads.com/quotes/2690-i-went-to-the-woods-because-i-wished-to-live"
							target="_blank"
							rel="noopener noreferrer"
							className="inline-block bg-linear-to-r from-blue-500 via-green-500 to-indigo-500 bg-clip-text text-transparent hover:from-blue-600 hover:via-green-600 hover:to-indigo-600">
							live deliberately
						</a>
						. Before research, I spent some years doing software engineering. In
						the limit of my life, I am also trying to be a pianist,{" "}
						<a
							href="https://lists.lipeng.ac/subscription/form"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							wri
						</a>
						<a
							href="https://www.instagram.com/endings_be_damned"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							ter
						</a>
						,{" "}
						<a
							href="https://kukfm.com"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							podcaster
						</a>
						,{" "}
						<a
							href="https://www.cssdesignawards.com/sites/tony-he/43449"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							designer
						</a>
						, and{" "}
						<a
							href="https://www.linkedin.com/company/retrograde-labs"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							entrepreneur
						</a>
						<sup>[1]</sup>.
					</p>
					<p className="mt-8">
						<sup>[1]</sup>&nbsp;
						<a
							href="https://inretrograde.io"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							Retrograde Labs
						</a>{" "}
						is a research-driven startup building the trust layer for AI. Our
						research aims not only to uncover failure modes and address
						fundamental bottlenecks, but to translate those insights into
						systems that work in production and withstand the{" "}
						<a
							href="https://www.ycombinator.com/companies?industry=Security"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							test of the market
						</a>{" "}
						and real customers. We build, grow, and scale products that turn
						research ideas into practical solutions for real-world workflows.
					</p>
				</div>
			</section>
			<section className="mt-16">
				<h2
					id="research"
					className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="microscope" />
					</span>
					<span className="uppercase">Research Interests</span>
				</h2>
				<div className="mt-3.75 flex flex-col gap-y-2 px-1 text-justify text-3 leading-relaxed font-light tracking-wide wrap-break-word text-gray-500 underline-offset-[6px] lg:text-[17px] dark:text-gray-300">
					<p>
						TL;DR: I study how adversarial robustness breaks when AI is deployed
						into real-world systems, and how to build defenses that survive
						those conditions.
					</p>
					<hr className="mt-3.5 dark:border-gray-700" />
					<p className="mt-3.5">
						My research focuses on{" "}
						<strong className="font-medium">
							Trustworthy Machine Learning
						</strong>
						, with an emphasis on the robustness of LLMs and the security &amp;
						privacy of agentic AI systems. I study failures that appear when
						models are fine-tuned, approximated, compressed, connected to tools,
						exposed to external content, or placed in multi-agent, and other
						production ML pipeline settings. I am interested in{" "}
						<a
							href="https://genai.owasp.org/llm-top-10"
							target="_blank"
							rel="noopener noreferrer"
							className="text-blue-500 hover:underline">
							issues
						</a>{" "}
						such as data leakage, prompt injection, unsafe tool use, brittle
						safety behavior, and systems that cannot explain or audit what
						happened.
					</p>
					<p className="mt-3.5">
						I develop effective and efficient adversarial attacks (e.g.,
						automated red-teaming), as well as principled/scalable defenses
						(e.g., based on reinforcement learning, mechanistic
						interpretability), drawing on applied cryptography, theoretical
						machine learning, and systems security to characterize and mitigate
						emerging threats.
					</p>
				</div>
			</section>
			<section className="mt-16">
				<h2
					id="research-vision"
					className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
					<span className="mr-1.5 flex h-5 w-5">
						<Icon name="rainbow" />
					</span>
					<span className="uppercase">Research Vision</span>
				</h2>
				<div className="mt-3.75 flex flex-col gap-y-2 px-1 text-justify text-3 leading-relaxed font-light tracking-wide wrap-break-word text-gray-500 underline-offset-[6px] lg:text-[17px] dark:text-gray-300">
					<p>
						To ensure AI&apos;s transformative potential reaches as much of the
						society as possible in the most beneficially impactful way, with
						safety as the unlock that guarantees the world will benefit from AI.
					</p>
				</div>
			</section>
			<section className="mt-16">
				<div className="flex items-center justify-between">
					<h2
						id="publications"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="newspaper" />
						</span>
						<span className="uppercase">
							<span className="hidden lg:inline">Selected </span>Publications
						</span>
					</h2>
					<span className="flex items-center gap-x-1 text-right text-gray-500 underline-offset-4 dark:text-gray-400">
						* indicates equal contribution
					</span>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					{PRIMARY_PUBLICATIONS.map((paper) => renderPaper(paper))}
				</div>
				<hr className="mt-5 dark:border-gray-700" />
				<div className="mt-5 flex flex-col gap-y-4">
					{OTHER_PUBLICATIONS.map((paper) => renderPaper(paper))}
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="preprints"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="article" />
						</span>
						<span className="block uppercase">Preprints</span>
					</h2>
					<Link
						href="https://scholar.google.com/citations?user=6yFlE_sAAAAJ"
						target="_blank"
						rel="noopener noreferrer"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						Citations
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					{PREPRINTS.map((paper) => renderPaper(paper))}
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="talks"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="personSpeaks" />
						</span>
						<span className="uppercase">Talks</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="Cybersecurity and Privacy Institute (CPI) Graduate Student Conference 2026"
						authors="Locket: Robust Feature-Locking Technique for Language Models"
						accepted={true}
						venue={{
							name: "Spotlight Talk",
							href: "https://uwaterloo.ca/cybersecurity-privacy-institute/2026gradstudentconference",
							color: "border-l-gray-500! border-l-4",
						}}
						links={[
							{
								label: "Poster",
								href: "https://static.ouorz.com/locket-cpi-poster.pdf",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="Cybersecurity and Privacy Institute (CPI) Graduate Student Conference 2025"
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
					<h2
						id="service"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="service" />
						</span>
						<span className="uppercase">Academic Services</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<ServiceCard
						serviceRole="Program Committee Member"
						serviceType="Conference"
						serviceTypeColor="border-l-red-400! border-l-4"
						serviceTitle={
							<>
								<p>The Web Conference (WWW) 2027</p>
								<p>USENIX Security Symposium (SEC) 2027</p>
							</>
						}
					/>
					<ServiceCard
						serviceRole="Artifact Evaluation Committee Member"
						serviceType="Conference"
						serviceTypeColor="border-l-red-400! border-l-4"
						serviceTitle={
							<>
								<p>USENIX Security Symposium (SEC) 2026</p>
								<p>
									ACM Conference on Computer and Communications Security (CCS)
									2025, 2026
								</p>
								<p>
									Privacy Enhancing Technologies Symposium (PoPETs/PETS) 2026
								</p>
							</>
						}
						serviceOrganization="🏆 USENIX SEC Noteworthy Reviewer, 2026"
					/>
					<ServiceCard
						serviceRole="Reviewer"
						serviceType="Journal"
						serviceTypeColor="border-l-red-400! border-l-4"
						serviceTitle={
							<>
								<p>
									IEEE Transactions on Knowledge and Data Engineering (TKDE),
									2026
								</p>
								<p>
									IEEE Transactions on Dependable and Secure Computing (TDSC),
									2026
								</p>
							</>
						}
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="mentoring"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="empathize" />
						</span>
						<span className="uppercase">Mentoring</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<ServiceCard
						serviceRole="Directed Reading Program (DRP) Mentor"
						serviceType="University"
						serviceTitle="AI Safety and Security Challenges in LLM-based Autonomous Agents (Spring 2026)"
						serviceOrganization="Women in Mathematics (WiM)"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="funding"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="money" />
						</span>
						<span className="uppercase">Funding</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<ServiceCard
						serviceRole="International Doctoral Student Award (IDSA)"
						serviceType="University"
						serviceTitle="CAD 5,100 per term"
						serviceOrganization="University of Waterloo"
					/>
					<ServiceCard
						serviceRole="University of Waterloo Graduate Scholarship"
						serviceType="University"
						serviceTitle="CAD 4,000"
						serviceOrganization="University of Waterloo"
					/>
					<ServiceCard
						serviceRole="AWS Startup Activate Credits (Portfolio)"
						serviceType="Industry"
						serviceTitle="USD 25,000"
						serviceOrganization="Amazon, Y Combinator"
					/>
					<ServiceCard
						serviceRole="Lambda Research Grant Program"
						serviceType="Industry"
						serviceTitle="USD 5,000; Principal Investigator: N. Asokan"
						serviceOrganization="λ (Lambda) AI"
					/>
					<ServiceCard
						serviceRole="David R. Cheriton Graduate Scholarship"
						serviceType="University"
						serviceTitle="CAD 20,000"
						serviceOrganization="University of Waterloo"
					/>
					<ServiceCard
						serviceRole="International Master's Award of Excellence (IMAE)"
						serviceType="University"
						serviceTitle="CAD 2,500 per term"
						serviceOrganization="University of Waterloo"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="teaching"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="presentation" />
						</span>
						<span className="uppercase">Teaching</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					{TEACHING_GROUPS.map((group) => (
						<ServiceCard
							key={group.institution}
							serviceLogoSrc={group.logoSrc}
							serviceLogoAlt={group.logoAlt}
							serviceRole={group.institution}
							serviceType={group.badge}
							serviceTitle={
								<ul className="divide-y divide-gray-100 dark:divide-gray-700/70">
									{group.courses.map((item) => (
										<li
											key={`${item.course}-${item.date}`}
											className="py-2 first:pt-0.5 last:pb-0.5">
											<p className="text-sm text-gray-700 dark:text-gray-200">
												{item.course}
											</p>
											<div className="mt-0.5 flex items-center justify-between gap-x-4 text-xs text-gray-500 dark:text-gray-400">
												<span>{item.role}</span>
												<time className="font-medium">{item.date}</time>
											</div>
										</li>
									))}
								</ul>
							}
						/>
					))}
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="research-experience"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="microscope" />
						</span>
						<span className="uppercase">Research Experience</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/crysp_logo.png"
						orgLogoAlt="Cryptography, Security, and Privacy (CrySP) Lab logo"
						organization="University of Waterloo"
						organizationFullName="Cryptography, Security, and Privacy (CrySP) Lab"
						jobTitle="Research Assistant (URA)"
						jobType="Research, Part-time"
						dateString="Jan 2025 - Present"
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
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="industry-experience"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="suitcase" />
						</span>
						<span className="uppercase">Industry Experience</span>
					</h2>
					<Link
						href="https://www.linkedin.com/in/~lhe/"
						target="_blank"
						rel="noopener noreferrer"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						LinkedIn
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/retrograde-labs-logo.png"
						organization="Retrograde Labs"
						organizationFullName="Accelerating scientific discovery and commercialization"
						jobTitle="Member of Technical Staff"
						jobType="Leadership"
						dateString="May 2026 - Present"
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
						jobTitle="Back End Software Engineer"
						jobType="SWE, Co-op"
						dateString="May - Aug 2022"
						organizationLocation="North York, ON"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/bitbuy_logo.png"
						organization="Bitbuy"
						organizationFullName="Cryptocurrency Exchange, Part of Robinhood, TSX: WNDR"
						jobTitle="Front End Software Engineer"
						jobType="SWE, Co-op"
						dateString="Sep - Dec 2021"
						organizationLocation="Toronto, ON"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<h2
						id="education"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="graduationCapOutline" />
						</span>
						<span className="uppercase">Education</span>
					</h2>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<div
						onScroll={(e) => {
							const target = e.currentTarget

							let nextMaskClass = ""
							if (
								target.scrollLeft > 0 &&
								target.scrollLeft < target.scrollWidth - target.clientWidth
							) {
								nextMaskClass = "mask-x-full"
							} else if (target.scrollLeft === 0) {
								nextMaskClass = "mask-x-r"
							} else {
								nextMaskClass = "mask-x-l"
							}

							setMaskClass(nextMaskClass)
						}}
						className={`flex gap-x-4 overflow-x-auto whitespace-nowrap ${maskClass}`}>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
							organization="University of Waterloo"
							organizationFullName="Computer Science"
							jobTitle="Doctor of Philosophy"
							jobType="PhD"
							dateString="Sep 2025 - Present"
						/>
						<EmploymentCard
							orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
							organization="University of Waterloo"
							organizationFullName="Mathematics (Minor in Computing)"
							jobTitle="Honours Bachelor's Degree (Co-op)"
							jobType="BMath"
							dateString="Sep 2020 - Apr 2025"
						/>
					</div>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/ntu_logo.jpeg"
						organization="Nanyang Technological University"
						organizationFullName="Mathematical Sciences"
						jobTitle="Exchange Student (GEM Trailblazer)"
						jobType="Undergrad"
						dateString="Aug 2023 - Dec 2023"
					/>
				</div>
			</section>
			<section className="mt-14 mb-24">
				<div className="flex justify-between">
					<h2
						id="newsletter"
						className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="edit" />
						</span>
						<span className="uppercase">Newsletter</span>
					</h2>
					<Link
						href="https://kukfm.com"
						target="_blank"
						rel="noopener noreferrer"
						className="flex items-center gap-x-1 text-gray-500 underline-offset-4 transition-colors hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500">
						Podcast
						<span className="h-5 w-5 underline">
							<Icon name="externalLink" />
						</span>
					</Link>
				</div>
				<div className="mt-5">
					<SubscriptionBox type="sm" />
				</div>
			</section>
		</>
	)
}

Home.layout = pageLayout

export default Home
