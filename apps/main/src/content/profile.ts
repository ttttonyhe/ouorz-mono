export interface PublicationLink {
	label: string
	href: string
	default?: boolean
}

export interface PublicationItem {
	title: string
	authors: string
	venue?: {
		name: string
		href: string
		color?: string
	}
	accepted: boolean
	datePublished?: string
	canonicalUrl?: string
	doi?: string
	links: PublicationLink[]
}

export interface ProjectItem {
	title: string
	description: string
	summary: string
	href: string
	repo?: string
	icon: string
	iconDark?: string
	className?: string
	applicationCategory: string
	operatingSystem?: string
}

export interface TeachingCourse {
	course: string
	role: string
	date: string
}

export interface TeachingGroup {
	institution: string
	badge: string
	logoSrc: string
	logoAlt: string
	courses: TeachingCourse[]
}

export interface TopicEntity {
	name: string
	wikidata: string
}

export interface ProfileLink {
	label: string
	href: string
	note: string
	relMe?: boolean
	inProfileMarkdown?: boolean
}

export interface StaticRouteEntry {
	path: string
	sitemap: boolean
	sources?: readonly string[]
}

export const STATIC_ROUTES: readonly StaticRouteEntry[] = [
	{ path: "/", sitemap: true },
	{ path: "/pages", sitemap: true },
	{
		path: "/links",
		sitemap: true,
		sources: ["src/pages/links/index.tsx", "content/posts"],
	},
	{
		path: "/podcasts",
		sitemap: true,
		sources: ["src/pages/podcasts/index.tsx", "content/posts"],
	},
	{
		path: "/reading-list",
		sitemap: true,
		sources: ["src/pages/reading-list/index.tsx"],
	},
	{
		path: "/sponsor",
		sitemap: true,
		sources: ["src/pages/sponsor/index.tsx", "content/data/sponsors.json"],
	},
	{
		path: "/web3",
		sitemap: true,
		sources: ["src/pages/web3/index.tsx", "content/data/sponsors.json"],
	},
	{ path: "/dashboard", sitemap: false },
]

export const SITE_URL = "https://lipeng.ac"
export const PERSON_ID = `${SITE_URL}/#person`
export const WEBSITE_ID = `${SITE_URL}/#website`
export const SITE_NAME = "Tony (Lipeng) He"
export const PROFILE_TITLE =
	"Tony (Lipeng) He (贺莉朋) | Trustworthy AI & CS PhD, UWaterloo"
export const PROFILE_DESCRIPTION =
	"Tony (Lipeng) He (贺莉朋) is a CS PhD student at the University of Waterloo (CrySP, SSG) researching Trustworthy AI and LLM security, building at Retrograde Labs."
export const PROFILE_UPDATED_AT = "2026-11-21"

export const PROFILE_LINKS: readonly ProfileLink[] = [
	{
		label: "Google Scholar",
		href: "https://scholar.google.com/citations?user=6yFlE_sAAAAJ",
		note: "Publication record.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "ORCID",
		href: "https://orcid.org/0009-0002-1802-7394",
		note: "Researcher identifier (0009-0002-1802-7394).",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "Semantic Scholar",
		href: "https://www.semanticscholar.org/author/2279554392",
		note: "Author profile.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "ACL Anthology",
		href: "https://aclanthology.org/people/lipeng-he/",
		note: "Verified ACL author record.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "Web of Science",
		href: "https://www.webofscience.com/wos/author/record/IUP-4165-2023",
		note: "ResearcherID IUP-4165-2023.",
	},
	{
		label: "Scopus",
		href: "https://www.scopus.com/authid/detail.uri?authorId=58848264500",
		note: "Author ID 58848264500.",
	},
	{
		label: "GitHub",
		href: "https://github.com/ttttonyhe",
		note: "Source code and open-source projects.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "LinkedIn",
		href: "https://www.linkedin.com/in/~lhe",
		note: "Industry and academic background.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "X",
		href: "https://x.com/ttttonyhe",
		note: "Public posts.",
		relMe: true,
		inProfileMarkdown: true,
	},
	{
		label: "YouTube",
		href: "https://www.youtube.com/@ttttonyhe",
		note: "Videos.",
		relMe: true,
		inProfileMarkdown: true,
	},
]

export const PERSON_IDENTITY = {
	name: "Lipeng He",
	displayName: SITE_NAME,
	givenName: "Lipeng",
	familyName: "He",
	additionalName: "Tony",
	chineseName: "贺莉朋",
	alternateNames: ["Tony He", "Tony (Lipeng) He", "Tony L. He", "贺莉朋"],
	jobTitle: "PhD Student in Computer Science",
	email: "lipeng.he@uwaterloo.ca",
	orcid: "0009-0002-1802-7394",
	orcidUrl: "https://orcid.org/0009-0002-1802-7394",
	office:
		"DC 3333, William G. Davis Computer Research Centre, University of Waterloo, Waterloo, ON N2L 3G1, Canada",
	cvUrl: "https://static.ouorz.com/tony_lipeng_he_cv_v3.pdf",
	sameAs: PROFILE_LINKS.map((link) => link.href),
	relMe: PROFILE_LINKS.filter((link) => link.relMe).map((link) => link.href),
} as const

export const RESEARCH_TOPICS: readonly TopicEntity[] = [
	{
		name: "Trustworthy AI",
		wikidata: "https://www.wikidata.org/wiki/Q115805554",
	},
	{
		name: "Machine Learning",
		wikidata: "https://www.wikidata.org/wiki/Q2539",
	},
	{
		name: "Adversarial Machine Learning",
		wikidata: "https://www.wikidata.org/wiki/Q20312394",
	},
	{
		name: "Large Language Models",
		wikidata: "https://www.wikidata.org/wiki/Q115305900",
	},
	{
		name: "AI Safety",
		wikidata: "https://www.wikidata.org/wiki/Q116291231",
	},
	{
		name: "Prompt Injection",
		wikidata: "https://www.wikidata.org/wiki/Q116737628",
	},
	{
		name: "Computer Security",
		wikidata: "https://www.wikidata.org/wiki/Q3510521",
	},
	{
		name: "Cryptography",
		wikidata: "https://www.wikidata.org/wiki/Q8789",
	},
	{
		name: "AI Agents",
		wikidata: "https://www.wikidata.org/wiki/Q1142726",
	},
	{
		name: "Reinforcement Learning",
		wikidata: "https://www.wikidata.org/wiki/Q830687",
	},
	{
		name: "Mechanistic Interpretability",
		wikidata: "https://www.wikidata.org/wiki/Q134503305",
	},
]

export const AWARDS: readonly string[] = [
	"USENIX Security Noteworthy Reviewer (2026)",
	"International Doctoral Student Award (IDSA), University of Waterloo",
	"David R. Cheriton Graduate Scholarship, University of Waterloo",
	"University of Waterloo Graduate Scholarship",
	"International Master's Award of Excellence (IMAE), University of Waterloo",
	"AWS Startup Activate Credits (Portfolio), Amazon and Y Combinator",
	"Lambda Research Grant Program, Lambda AI",
]

export const PRIMARY_PUBLICATIONS: readonly PublicationItem[] = [
	{
		title: "SoK: Colluding Adversaries in Machine Learning Pipelines",
		authors: "Vasisht Duddu, Lipeng He, Asim Waheed, and N. Asokan",
		accepted: true,
		datePublished: "2026-06-08",
		canonicalUrl:
			"https://www.usenix.org/conference/usenixsecurity26/presentation/duddu",
		venue: {
			name: "USENIX Security 2026",
			href: "https://www.usenix.org/conference/usenixsecurity26",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2606.10091",
				default: true,
			},
		],
	},
	{
		title: "Understanding and Preserving Safety in Fine-Tuned LLMs",
		authors:
			"Jiawen Zhang, Yangfan Hu, Kejia Chen, Lipeng He, Jiachen Ma, Jian Lou, Dan Li, Jian Liu, Xiaohu Yang, and Ruoxi Jia",
		accepted: true,
		datePublished: "2026-11-14",
		doi: "10.1145/3830454.3832677",
		canonicalUrl: "https://doi.org/10.1145/3830454.3832677",
		venue: {
			name: "CCS 2026",
			href: "https://www.sigsac.org/ccs/CCS2026/index.html",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2601.10141",
				default: true,
			},
		],
	},
	{
		title: "Locket: Robust Feature-Locking Technique for Language Models",
		authors: "Lipeng He, Vasisht Duddu, and N. Asokan",
		accepted: true,
		datePublished: "2026-07-01",
		doi: "10.18653/v1/2026.acl-long.626",
		canonicalUrl: "https://aclanthology.org/2026.acl-long.626/",
		venue: {
			name: "ACL 2026",
			href: "https://2026.aclweb.org",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2510.12117",
				default: true,
			},
			{
				label: "Poster",
				href: "https://static.ouorz.com/locket-cpi-poster.pdf",
			},
			{
				label: "Workshop",
				href: "https://openreview.net/forum?id=1JBK4x8ik9",
			},
			{
				label: "Code",
				href: "https://github.com/ssg-research/locket",
			},
		],
	},
	{
		title:
			"Safety at One Shot: Patching Fine-Tuned LLMs with A Single Instance",
		authors:
			"Jiawen Zhang, Lipeng He, Kejia Chen, Jian Lou, Jian Liu, Xiaohu Yang, and Ruoxi Jia",
		accepted: true,
		datePublished: "2026-01-04",
		canonicalUrl: "https://openreview.net/forum?id=EyH8Fu3vtZ",
		venue: {
			name: "ICLR 2026",
			href: "https://iclr.cc",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2601.01887",
				default: true,
			},
			{
				label: "Code",
				href: "https://github.com/Kevin-Zh-CS/safety-at-one-shot",
			},
		],
	},
	{
		title:
			"Activation Approximations Can Incur Safety Vulnerabilities Even in Aligned LLMs: Comprehensive Analysis and Defense",
		authors:
			"Jiawen Zhang*, Kejia Chen*, Lipeng He*, Jian Lou, Dan Li, Zunlei Feng, Mingli Song, Jian Liu, Kui Ren, and Xiaohu Yang",
		accepted: true,
		datePublished: "2025-08-13",
		canonicalUrl:
			"https://www.usenix.org/conference/usenixsecurity25/presentation/zhang-jiawen",
		venue: {
			name: "USENIX Security 2025",
			href: "https://www.usenix.org/conference/usenixsecurity25",
			color: "border-l-red-500! border-l-4",
		},
		links: [
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
		],
	},
	{
		title:
			"LookAhead: Preventing DeFi Attacks via Unveiling Adversarial Contracts",
		authors:
			"Shoupeng Ren, Lipeng He, Tianyu Tu, Di Wu, Jian Liu, Kui Ren, and Chun Chen",
		accepted: true,
		datePublished: "2025-06-19",
		doi: "10.1145/3729353",
		canonicalUrl: "https://doi.org/10.1145/3729353",
		venue: {
			name: "FSE 2025",
			href: "https://conf.researchr.org/home/fse-2025",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2401.07261",
				default: true,
			},
			{
				label: "Code",
				href: "https://github.com/zju-abclab/LookAhead",
			},
		],
	},
	{
		title: "Secure Transformer Inference Made Non-interactive",
		authors:
			"Jiawen Zhang, Xinpeng Yang, Lipeng He, Kejia Chen, Wen-jie Lu, Yinghao Wang, Xiaoyang Hou, Jian Liu, Kui Ren and Xiaohu Yang",
		accepted: true,
		datePublished: "2025-02-24",
		doi: "10.14722/ndss.2025.230868",
		canonicalUrl:
			"https://www.ndss-symposium.org/ndss-paper/secure-transformer-inference-made-non-interactive/",
		venue: {
			name: "NDSS 2025",
			href: "https://www.ndss-symposium.org/ndss2025/",
			color: "border-l-red-500! border-l-4",
		},
		links: [
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
				label: "🏆 Top-100 Security Papers",
				href: "https://www.mlsec.org/topnotch/sec_ntop100.html",
			},
		],
	},
	{
		title: "On the Atomicity and Efficiency of Blockchain Payment Channels",
		authors:
			"Di Wu, Shoupeng Ren, Yuman Bai, Lipeng He, Jian Liu, Wu Wen, Kui Ren, et al.",
		accepted: true,
		datePublished: "2025-08-13",
		canonicalUrl:
			"https://www.usenix.org/conference/usenixsecurity25/presentation/wu-di",
		venue: {
			name: "USENIX Security 2025",
			href: "https://www.usenix.org/conference/usenixsecurity25",
			color: "border-l-red-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://eprint.iacr.org/2025/180",
				default: true,
			},
			{
				label: "Code",
				href: "https://github.com/zju-abclab/ultraviolet",
			},
		],
	},
]

export const OTHER_PUBLICATIONS: readonly PublicationItem[] = [
	{
		title: "Revealing and Benchmarking the Safety Risks in Blockchain Agents",
		authors:
			"Jiawen Zhang, Kejia Chen, Lipeng He, Yechao Zhang, Jian Liu and Xiaohu Yang",
		accepted: true,
		venue: {
			name: "BCRA 2026",
			href: "https://bcra-conf.seedsbit.com/",
			color: "border-l-gray-500! border-l-4",
		},
		links: [],
	},
	{
		title:
			"StructEval: Benchmarking LLMs' Capabilities to Generate Structural Outputs",
		authors:
			"Jialin Yang, Dongfu Jiang, Lipeng He, Sherman Siu, Yuxuan Zhang, Disen Liao, Benjamin Schneider, Ping Nie, Wenhu Chen, et al.",
		accepted: true,
		datePublished: "2025-05-26",
		canonicalUrl: "https://openreview.net/forum?id=buDwV7LUA7",
		venue: {
			name: "Transactions on Machine Learning Research",
			href: "https://jmlr.org/tmlr",
			color: "border-l-gray-500! border-l-4",
		},
		links: [
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
			},
			{
				label: "🏆 J2C",
				href: "https://neurips.cc/public/JournalToConference",
			},
		],
	},
	{
		title:
			"FedVLP: Visual-aware Latent Prompt Generation for Multimodal Federated Learning",
		authors:
			"Hao Pan, Xiaoli Zhao, Yuchen Jiang, Lipeng He, Bingquan Wang, and Yincan Shu",
		accepted: true,
		datePublished: "2025-09-01",
		doi: "10.1016/j.cviu.2025.104442",
		canonicalUrl: "https://doi.org/10.1016/j.cviu.2025.104442",
		venue: {
			name: "Computer Vision and Image Understanding",
			href: "https://www.sciencedirect.com/journal/computer-vision-and-image-understanding",
			color: "border-l-yellow-400! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://www.sciencedirect.com/science/article/abs/pii/S1077314225001651",
				default: true,
			},
		],
	},
	{
		title:
			"A Survey of Multimodal Federated Learning: Background, Applications, and Perspectives",
		authors: "Hao Pan, Xiaoli Zhao, Lipeng He, Yicong Shi, and Xiaogang Lin",
		accepted: true,
		datePublished: "2024-07-29",
		doi: "10.1007/s00530-024-01422-9",
		canonicalUrl:
			"https://link.springer.com/article/10.1007/s00530-024-01422-9",
		venue: {
			name: "Multimedia Systems",
			href: "https://link.springer.com/journal/530",
			color: "border-l-green-600! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://link.springer.com/article/10.1007/s00530-024-01422-9",
				default: true,
			},
			{
				label: "Code",
				href: "https://github.com/haopr/MMFL",
			},
		],
	},
	{
		title:
			"A Comparative Examination of Network and Contract-Based Blockchain Storage Solutions for Decentralized Applications",
		authors: "Lipeng He",
		accepted: true,
		datePublished: "2023-12-04",
		doi: "10.2991/978-94-6463-304-7_16",
		canonicalUrl:
			"https://www.atlantis-press.com/proceedings/deca-23/125994999",
		venue: {
			name: "DECA 2023",
			href: "https://www.atlantis-press.com/proceedings/deca-23",
			color: "border-l-gray-500! border-l-4",
		},
		links: [
			{
				label: "Paper",
				href: "https://www.atlantis-press.com/proceedings/deca-23/125994999",
				default: true,
			},
		],
	},
]

export const ALL_PUBLICATIONS: readonly PublicationItem[] = [
	...PRIMARY_PUBLICATIONS,
	...OTHER_PUBLICATIONS,
]

export const PREPRINTS: readonly PublicationItem[] = [
	{
		title: "PIPETTE: Prompt Injection Purple-Teaming for Robust LLM Agents",
		authors: "Lipeng He, Yihan Wang, Jiawen Zhang, and N. Asokan",
		accepted: false,
		datePublished: "2026-11-18",
		doi: "10.48550/arXiv.2611.24362",
		canonicalUrl: "https://arxiv.org/abs/2611.24362",
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2611.24362",
				default: true,
			},
		],
	},
	{
		title:
			"Designing Deceptive Model Organisms that Successfully Evade Auditing",
		authors: "Vasisht Duddu, Mark Wang, Lipeng He, and Tim G. J. Rudner",
		accepted: false,
		datePublished: "2026-11-16",
		doi: "10.48550/arXiv.2611.20459",
		canonicalUrl: "https://arxiv.org/abs/2611.20459",
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2611.20459",
				default: true,
			},
		],
	},
	{
		title:
			"SecStep: Guarding Agents Against Prompt Injection via Action Backtracking",
		authors:
			"Jiawen Zhang, Yechao Zhang, Lipeng He, Kejia Chen, Jiachen Ma, Yangfan Hu, Jian Lou, Jian Liu, Xiaohu Yang, and Tianwei Zhang",
		accepted: false,
		links: [],
	},
	{
		title:
			"Beyond Similarity: Trustworthy Memory Search for Personal AI Agents",
		authors:
			"Jiawen Zhang, Kejia Chen, Jiachen Ma, Yangfan Hu, Lipeng He, Yechao Zhang, Jian Liu, Xiaohu Yang, Tianwei Zhang, and Ruoxi Jia",
		accepted: false,
		datePublished: "2026-06-04",
		doi: "10.48550/arXiv.2606.06054",
		canonicalUrl: "https://arxiv.org/abs/2606.06054",
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2606.06054",
				default: true,
			},
		],
	},
	{
		title:
			"Defending against Adaptive Prompt Injection Attacks via Reasoning-enabled Task Alignment",
		authors: "Lipeng He, Yihan Wang, Jiawen Zhang, and N. Asokan",
		accepted: false,
		datePublished: "2026-06-13",
		doi: "10.48550/arXiv.2606.15441",
		canonicalUrl: "https://arxiv.org/abs/2606.15441",
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2606.15441",
				default: true,
			},
		],
	},
	{
		title: "Backdooring Bias in Large Language Models",
		authors:
			"Anudeep Das, Prach Chantasantitam, Gurjot Singh, Lipeng He, Mariia Ponomarenko, and Florian Kerschbaum",
		accepted: false,
		datePublished: "2026-02-13",
		doi: "10.48550/arXiv.2602.13427",
		canonicalUrl: "https://arxiv.org/abs/2602.13427",
		links: [
			{
				label: "Paper",
				href: "https://arxiv.org/abs/2602.13427",
				default: true,
			},
		],
	},
	{
		title:
			"From Detection to Diagnosis: Lightweight Federated Prompt Learning for Interpretable Industrial Anomaly Analysis",
		authors: "Hao Pan, Xiaoli Zhao, Lipeng He, and Xiwu Shang",
		accepted: false,
		links: [],
	},
	{
		title:
			"Token-by-Token Manipulation: Inference-Time Jailbreaking on Production LLMs via Autoregressive Harmful Guidance",
		authors:
			"Jiawen Zhang, Lipeng He, Kejia Chen, Jian Liu, Zunlei Feng, Mingli Song, Jian Lou, Dan Li, and Xiaohu Yang",
		accepted: false,
		links: [],
	},
]

export const PROJECTS: readonly ProjectItem[] = [
	{
		title: "Lune",
		description: "AI research, free of AI slop",
		summary:
			"Scientific research search engine and MCP server grounding AI agents in full-text peer-reviewed computer science papers from top venues.",
		href: "https://luneresearch.com",
		repo: "https://github.com/RetrogradeLabs/lune-mcp-server",
		icon: "https://luneresearch.com/lune-logo.svg",
		applicationCategory: "DeveloperApplication",
	},
	{
		title: "Retroguard",
		description: "Verifiably secure AI guardrails",
		summary:
			"AI security guardrails built by Retrograde Labs to block prompt injection, data leaks, and off-policy outputs with verifiable isolation.",
		href: "https://retroguard.ai",
		icon: "https://retroguard.ai/logo.svg",
		className: "text-purple-500",
		applicationCategory: "SecurityApplication",
	},
	{
		title: "ProChoice",
		description: "No-nonsense LLM leaderboards",
		summary:
			"Open-source leaderboards comparing frontier LLMs and coding agent harnesses across composite indices and live benchmarks by score and real dollar cost.",
		href: "https://prochoice.industries",
		repo: "https://github.com/RetrogradeLabs/pro-choice",
		icon: "https://prochoice.industries/icon.svg",
		applicationCategory: "DeveloperApplication",
	},
	{
		title: "Tailspace",
		description: "Run Tailscale in rootless mode",
		summary:
			"Install and supervise rootless userspace Tailscale on Linux without sudo, /dev/net/tun, or systemd, built for HPC, Slurm, Kubernetes pods, and shared servers.",
		href: "https://ttttonyhe.github.io/tailspace",
		repo: "https://github.com/ttttonyhe/tailspace",
		icon: "https://ttttonyhe.github.io/tailspace/assets/logo-light.svg",
		iconDark: "https://ttttonyhe.github.io/tailspace/assets/logo-dark.svg",
		applicationCategory: "DeveloperApplication",
		operatingSystem: "Linux",
	},
	{
		title: "Snapod",
		description: "PaaS for podcasters",
		summary: "Podcast hosting and publishing platform for independent shows.",
		href: "https://www.snapodcast.com",
		icon: "🎙️",
		applicationCategory: "MultimediaApplication",
	},
	{
		title: "Autogrind",
		description: "24x7 auto-work mode skill",
		summary:
			"Open-source skill for autonomous continuous coding agent execution across Claude Code, Cursor, Gemini CLI, Codex, and OpenCode.",
		href: "https://github.com/ttttonyhe/autogrind",
		repo: "https://github.com/ttttonyhe/autogrind",
		icon: "✊",
		applicationCategory: "DeveloperApplication",
	},
]

export const TEACHING_GROUPS: readonly TeachingGroup[] = [
	{
		institution: "University of Waterloo",
		badge: "University",
		logoSrc: "https://static.ouorz.com/uwaterloo_logo.webp",
		logoAlt: "University of Waterloo logo",
		courses: [
			{
				course:
					"CS 858 Trustworthy Machine Learning: a Systems Security Approach",
				role: "Course Staff (Expert)",
				date: "Sep 2026 - Present",
			},
			{
				course: "CS 436 Networks and Distributed Computer Systems",
				role: "Teaching Assistant (TA)",
				date: "Jan 2026 - Apr 2026",
			},
			{
				course: "CS 135 Designing Functional Programs",
				role: "Instructional Apprentice (IA)",
				date: "Sep 2025 - Dec 2025",
			},
			{
				course: "CS 135 Designing Functional Programs",
				role: "Instructional Support Assistant (ISA)",
				date: "Aug 2024 - Dec 2024",
			},
		],
	},
]
