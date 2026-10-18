import { upgrades } from "@openzeppelin/hardhat-upgrades"
import hre, { network } from "hardhat"

const uri = process.env.BLOG_URI?.trim()
if (!uri) throw new Error("BLOG_URI is not set")

const connection = await network.create()
try {
	const upgradesApi = await upgrades(hre, connection)
	const factory = await connection.ethers.getContractFactory("TwilightBlog")
	const blog = await upgradesApi.deployProxy(factory, [uri], {
		kind: "transparent",
	})
	await blog.waitForDeployment()

	const proxy = await blog.getAddress()
	console.log(
		JSON.stringify(
			{
				chainId: (
					await connection.ethers.provider.getNetwork()
				).chainId.toString(),
				proxy,
				admin: await upgradesApi.erc1967.getAdminAddress(proxy),
				implementation:
					await upgradesApi.erc1967.getImplementationAddress(proxy),
			},
			null,
			2
		)
	)
} finally {
	await connection.close()
}
