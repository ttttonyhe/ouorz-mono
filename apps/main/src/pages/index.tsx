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
						<h1 className="wrap-break-word flex items-center whitespace-nowrap font-medium text-3xl text-black tracking-wide lg:text-[1.8rem] dark:text-white">
							<span className="mr-2 inline-block animate-wave-hand cursor-pointer hover:animate-wave-hand-again">
								👋
							</span>
							Tony (Lipeng) He
						</h1>
						<div className="wrap-break-word flex flex-col gap-y-1.5 px-1 font-light text-4 text-gray-500 leading-relaxed tracking-wider lg:text-2 dark:text-gray-300">
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
				<div className="wrap-break-word mt-[15px] flex flex-col px-1 text-justify font-light text-3 text-gray-500 leading-relaxed tracking-wide underline-offset-[6px] lg:text-left lg:text-[17px] dark:text-gray-300">
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
						<a
							href="https://www.goodreads.com/quotes/2690-i-went-to-the-woods-because-i-wished-to-live"
							target="_blank"
							rel="noreferrer"
							className="inline-block bg-linear-to-r from-blue-500 via-green-500 to-indigo-500 bg-clip-text text-transparent hover:from-blue-600 hover:via-green-600 hover:to-indigo-600">
							live deliberately
						</a>
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
						,{" "}
						<a
							href="https://www.cssdesignawards.com/sites/tony-he/43449"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							designer
						</a>
						, and{" "}
						<a
							href="https://www.producthunt.com/products/snapod-beta"
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
				<div className="wrap-break-word mt-[15px] flex flex-col gap-y-2 px-1 text-justify font-light text-3 text-gray-500 leading-relaxed tracking-wide underline-offset-[6px] lg:text-[17px] dark:text-gray-300">
					<p>
						My research focuses on <strong>Trustworthy Machine Learning</strong>
						, with an emphasis on the adversarial robustness of{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://genai.owasp.org/llm-top-10"
							target="_blank"
							rel="noreferrer">
							large language models (LLMs)
						</a>
						, and the security &amp; privacy of modern Artificial Intelligence
						(AI) systems. I develop effective and efficient{" "}
						<strong>adversarial attacks</strong>, as well as{" "}
						<strong>principled defenses</strong>, drawing on applied
						cryptography, theoretical machine learning, and computer security to
						characterize and mitigate emerging threats.
					</p>
					<p className="mt-3.5">
						More broadly, I am interested in{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://www.anthropic.com/research/team/alignment"
							target="_blank"
							rel="noreferrer">
							alignment
						</a>
						,{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://www.goodfire.ai/research"
							target="_blank"
							rel="noreferrer">
							interpretability
						</a>
						, and{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://www.alignmentforum.org/w/reinforcement-learning"
							target="_blank"
							rel="noreferrer">
							reinforcement learning
						</a>{" "}
						for building controllable and reliable AI systems.
					</p>
					<p className="mt-3.5">
						I also study the design and <strong>software engineering</strong> of
						agentic systems for both{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://rdi.berkeley.edu/frontier-ai-impact-on-cybersecurity/index.html"
							target="_blank"
							rel="noreferrer">
							AI-for-security
						</a>{" "}
						use cases and real-world business applications, with a particular
						focus on the security and privacy of{" "}
						<a
							className="text-blue-500 hover:underline"
							href="https://www.ibm.com/think/topics/ai-agent-security"
							target="_blank"
							rel="noreferrer">
							LLM-based agents
						</a>{" "}
						and multi-agent systems.
					</p>
					<p className="mt-3.5">
						A central goal of my work is to leverage theoretical security
						research to address{" "}
						<strong>bottlenecks in real-world production systems</strong>. I
						look for ways to bridge research efforts with practical deployments
						and{" "}
						<a
							href="https://www.ycombinator.com/companies?industry=Security"
							target="_blank"
							rel="noreferrer"
							className="text-blue-500 hover:underline">
							viable business models
						</a>
						, thus enabling more trustworthy AI in practice.
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
						title="Safety at One Shot: Patching Fine-Tuned LLMs with A Single Instance"
						authors="Jiawen Zhang, Lipeng He, Kejia Chen, Jian Lou, Jian Liu, Xiaohu Yang, and Ruoxi Jia"
						accepted={true}
						venue={{
							name: "ICLR 2026",
							href: "https://iclr.cc",
							color: "border-l-red-500! border-l-4",
						}}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2601.01887",
								default: true,
							},
							{
								label: "Code",
								href: "https://github.com/Kevin-Zh-CS/safety-at-one-shot",
							},
						]}
					/>
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
								href: "https://www.usenix.org/system/files/usenixsecurity25-zhang-jiawen.pdf",
								default: true,
							},
							{
								label: "Code",
								href: "https://github.com/Kevin-Zh-CS/QuadA",
							},
							{
								label: "Website",
								href: "https://kevin-zh-cs.github.io/QuadA",
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
							{
								label: "🏆 Top-cited",
								href: "https://www.mlsec.org/topnotch/sec_2020s.html",
							},
						]}
					/>
					<PaperCard
						title="On the Atomicity and Efficiency of Blockchain Payment Channels"
						authors="Di Wu, Shoupeng Ren, Yuman Bai, Lipeng He, Jian Liu, Wu Wen, Kui Ren, et al."
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
							{
								label: "Code",
								href: "https://github.com/zju-abclab/ultraviolet",
							},
						]}
					/>
				</div>
				<hr className="mt-5 dark:border-gray-700" />
				<div className="mt-5 flex flex-col gap-y-4">
					<PaperCard
						title="StructEval: Benchmarking LLMs' Capabilities to Generate Structural Outputs"
						authors="Jialin Yang, Dongfu Jiang, Lipeng He, Sherman Siu, Yuxuan Zhang, Disen Liao, Benjamin Schneider, Ping Nie, Wenhu Chen, et al."
						accepted={true}
						venue={{
							name: "Transactions on Machine Learning Research",
							href: "https://jmlr.org/tmlr",
							color: "border-l-gray-500! border-l-4",
						}}
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
							{
								label: "🏆 J2C",
								href: "https://neurips.cc/public/JournalToConference",
							},
						]}
					/>
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
						authors="Hao Pan, Xiaoli Zhao, Lipeng He, Yicong Shi, and Xiaogang Lin"
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
						title="Understanding and Preserving Safety in Fine-Tuned LLMs"
						authors="Jiawen Zhang, Yangfan Hu, Kejia Chen, Lipeng He, Jiachen Ma, Jian Lou, Dan Li, Jian Liu, Xiaohu Yang, and Ruoxi Jia"
						accepted={false}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2601.10141",
								default: true,
							},
						]}
					/>
					<PaperCard
						title="Locket: Robust Feature-Locking Technique for Language Models"
						authors="Lipeng He, Vasisht Duddu, and N. Asokan"
						accepted={false}
						links={[
							{
								label: "Paper",
								href: "https://arxiv.org/abs/2510.12117",
								default: true,
							},
							{
								label: "Poster",
								href: "https://static.ouorz.com/locket-cpi-poster.pdf",
							},
						]}
					/>
					<PaperCard
						title="From Detection to Diagnosis: Lightweight Federated Prompt Learning for Interpretable Industrial Anomaly Analysis"
						authors="Hao Pan, Xiaoli Zhao, Lipeng He, and Xiwu Shang"
						accepted={false}
						links={[]}
					/>
					<PaperCard
						title="Token-by-Token Manipulation: Inference-Time Jailbreaking on Production LLMs via Autoregressive Harmful Guidance"
						authors="Jiawen Zhang, Lipeng He, Kejia Chen, Jian Liu, Zunlei Feng, Mingli Song, Jian Lou, Dan Li, and Xiaohu Yang"
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
						serviceTypeColor="border-l-red-400! border-l-4"
						serviceTitle="USENIX Security Symposium 2026"
						serviceOrganization="Artifact Evaluation"
					/>
					<ServiceCard
						serviceRole="Program Committee Member"
						serviceType="Conference"
						serviceTypeColor="border-l-green-600! border-l-4"
						serviceTitle="Privacy Enhancing Technologies Symposium (PoPETs/PETS) 2026"
						serviceOrganization="Artifact Evaluation"
					/>
					<ServiceCard
						serviceRole="Program Committee Member"
						serviceType="Conference"
						serviceTypeColor="border-l-red-400! border-l-4"
						serviceTitle="ACM Conference on Computer and Communications Security (CCS) 2025"
						serviceOrganization="Artifact Evaluation"
					/>
					<ServiceCard
						serviceRole="Invited Reviewer"
						serviceType="Journal"
						serviceTypeColor="border-l-red-400! border-l-4"
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
							<Icon name="money" />
						</span>
						<span className="uppercase">Funding</span>
					</label>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
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
						serviceTitle="CAD 10,000"
						serviceOrganization="University of Waterloo"
					/>
					<ServiceCard
						serviceRole="International Master's Award of Excellence (IMAE)"
						serviceType="University"
						serviceTitle="CAD 7,500"
						serviceOrganization="University of Waterloo"
					/>
				</div>
			</section>
			<section className="mt-14">
				<div className="flex items-center justify-between">
					<label className="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-[4px] font-medium tracking-wider shadow-xs dark:border-gray-600 dark:bg-gray-700">
						<span className="mr-1.5 flex h-5 w-5">
							<Icon name="presentation" />
						</span>
						<span className="uppercase">Teaching</span>
					</label>
				</div>
				<div className="mt-5 flex flex-col gap-y-4">
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
						organization="University of Waterloo"
						organizationFullName="CS 436 Networks and Distributed Computer Systems"
						jobTitle="Teaching Assistant (TA)"
						jobType="Part-time"
						dateString="Jan 2026 - Present"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
						organization="University of Waterloo"
						organizationFullName="CS 135 Designing Functional Programs"
						jobTitle="Instructional Apprentice (IA)"
						jobType="Part-time"
						dateString="Sept 2025 - Dec 2025"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/uwaterloo_logo.webp"
						organization="University of Waterloo"
						organizationFullName="CS 135 Designing Functional Programs"
						jobTitle="Instructional Support Assistant (ISA)"
						jobType="Co-op"
						dateString="Aug 2024 - Dec 2024"
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
						orgLogoSrc="https://static.ouorz.com/ezra_logo.jpg"
						organization="Bluelet AI"
						organizationFullName="Agentic AI and data platform solutions for talent acquisition and matching"
						jobTitle="Co-Founder & CTO"
						jobType="Leadership"
						dateString="May 2025 - June 2025"
					/>
					<EmploymentCard
						orgLogoSrc="https://static.ouorz.com/crysp_logo.png"
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
							jobType="MMath"
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
