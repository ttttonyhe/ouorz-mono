"use client"

import { useState, useEffect } from "react"
import { Contract, Web3Provider, Provider, Signer } from "zksync-web3"
import { ethers } from "ethers"

import Address from "@ouorz/twilight-protocol/deployment/address.json"
import Artifact from "@ouorz/twilight-protocol/artifacts-zk/contracts/TwilightBlog.sol/TwilightBlog.json"

class BlogContractInterface {
	provider: Provider
	signer: Signer
	contract: Contract
	account: string

	constructor(selectedAddress: string) {
		this.provider = new Provider("https://testnet.era.zksync.dev")
		this.signer = new Web3Provider((window as any).ethereum).getSigner(
			selectedAddress
		)
		this.contract = new Contract(
			Address.TwilightBlogProxy,
			Artifact.abi,
			this.signer
		)
	}

	async getAuthor() {
		return await this.contract.author()
	}

	async getBlogUri() {
		return await this.contract.blogUri()
	}

	async categories() {
		const categoryDetails = await this.contract
			.categories()
			.then((categories: any) =>
				categories.map((category: any) => {
					const categoryDetail = {
						id: category.id.toString(),
						name: category.name,
						description: category.description,
					}
					return categoryDetail
				})
			)
		return categoryDetails
	}

	async updateBlogUri(blogUri: string) {
		await this.contract.updateBlogUri(blogUri)
	}

	async createNewCategory(id: number, name: string, description: string) {
		await this.contract.createCategory(id, name, description)
	}
}

const requestConnection = () => {
	;(window as any).ethereum.request({
		method: "eth_requestAccounts",
	})
}

const Page = () => {
	const [contractInterface, setContractInterface] =
		useState<BlogContractInterface>()
	const [account, setAccount] = useState<string>("")
	const [blogUri, setBlogUri] = useState<string>("")
	const [authorAddress, setAuthorAddress] = useState<string>("")
	const [categories, setCategories] = useState<ethers.utils.Result>()

	useEffect(() => {
		;(window as any).ethereum.on("accountsChanged", (accounts: string[]) => {
			setAccount(accounts[0])
		})
		setAccount((window as any).ethereum.selectedAddress)
		setContractInterface(
			new BlogContractInterface((window as any).ethereum.selectedAddress)
		)
	}, [])

	return (
		<main>
			<h1>Hello {account}</h1>
			<ul>
				<li>Blog uri: {blogUri}</li>
				<li>Author address: {authorAddress}</li>
				{categories?.map((category) => (
					<li key={category.id}>
						{category.name} : {category.description}
					</li>
				))}
			</ul>
			<button onClick={() => requestConnection()}>Connect</button>
			<button
				onClick={async () => {
					const blogUri = await contractInterface.getBlogUri()
					setBlogUri(blogUri)
				}}
			>
				Get blog uri
			</button>
			<button
				onClick={async () => {
					const authorAddress = await contractInterface.getAuthor()
					setAuthorAddress(authorAddress)
				}}
			>
				Get author address
			</button>
			<button
				onClick={async () => {
					const categories = await contractInterface.categories()
					console.log(categories)
					setCategories(categories)
				}}
			>
				Get categories
			</button>
			<button
				onClick={async () => {
					await contractInterface.updateBlogUri("newUri")
				}}
			>
				Update blog uri
			</button>
			<button
				onClick={async () => {
					await contractInterface.createNewCategory(
						2,
						"Cate2",
						"Cate2 description"
					)
				}}
			>
				Create new category
			</button>
		</main>
	)
}

export default Page
