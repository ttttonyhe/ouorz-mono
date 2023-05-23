import * as hre from "hardhat"
import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import { Wallet } from "zksync-web3"

import Contracts from "../constants/contracts"
import ContractConstructorArguments from "../constants/arguments"
import ContractAddresses from "../deployment/address.json"

async function main() {
	console.log("Upgrading Twilight Blog implementation contract...")

	const wallet = new Wallet(process.env.DEPLOYER_PRIVATE_KEY)
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
	await contract.deployed()

	console.log(
		`${artifact.contractName} has been upgraded, ` +
			"run verify:blog to verify the implementation contract"
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
