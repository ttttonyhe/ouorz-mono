import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import * as hre from "hardhat"
import { Wallet } from "zksync-ethers"

import ContractConstructorArguments from "../constants/arguments"
import Contracts from "../constants/contracts"
import ContractAddresses from "../deployment/address.json"
import { requireDeployerPrivateKey } from "./deployerWallet"

async function main() {
	console.log("Upgrading Twilight Blog implementation contract...")

	const wallet = new Wallet(requireDeployerPrivateKey())
	const deployer = new Deployer(hre, wallet)

	const artifact = await deployer.loadArtifact(Contracts.Blog)

	console.log(
		"Initializer arguments: ",
		JSON.stringify(ContractConstructorArguments.TwilightBlog)
	)

	const contract = await hre.zkUpgrades.upgradeProxy(
		deployer.zkWallet,
		ContractAddresses.TwilightBlogProxy,
		artifact
	)
	await contract.waitForDeployment()

	console.log(
		`${artifact.contractName} has been upgraded, ` +
			"run verify:blog to verify the implementation contract"
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
