import { upgrades } from "@openzeppelin/hardhat-upgrades"
import {
	getAddress,
	isError,
	TransactionResponse,
	type TransactionReceipt,
} from "ethers"
import hre, { network } from "hardhat"

const proxyAddress = getAddress(process.env.BLOG_PROXY_ADDRESS ?? "")
const connection = await network.create()
try {
	const upgradesApi = await upgrades(hre, connection)
	const factory = await connection.ethers.getContractFactory("TwilightBlog")
	const blog = await upgradesApi.upgradeProxy(proxyAddress, factory, {
		kind: "transparent",
	})
	const transaction = blog.deployTransaction
	if (!(transaction instanceof TransactionResponse)) {
		throw new Error("The upgrade did not return a transaction.")
	}

	let receipt: TransactionReceipt | null
	try {
		receipt = await transaction.wait(1, 120_000)
	} catch (error) {
		if (isError(error, "TRANSACTION_REPLACED") && !error.cancelled) {
			receipt = error.receipt
		} else if (isError(error, "TIMEOUT")) {
			throw new Error(
				`Upgrade transaction ${transaction.hash} is still pending. Check its status before retrying.`,
				{ cause: error }
			)
		} else {
			throw error
		}
	}
	if (receipt?.status !== 1)
		throw new Error("The upgrade transaction was not confirmed.")

	console.log(
		JSON.stringify(
			{
				chainId: (
					await connection.ethers.provider.getNetwork()
				).chainId.toString(),
				proxy: proxyAddress,
				implementation:
					await upgradesApi.erc1967.getImplementationAddress(proxyAddress),
			},
			null,
			2
		)
	)
} finally {
	await connection.close()
}
