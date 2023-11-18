import * as hre from "hardhat"
import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import { Wallet } from "zksync-web3"

import Contracts from "../constants/contracts"
import ContractConstructorArguments from "../constants/arguments"

async function main() {
	console.log(
		"Deploying Twilight Blog proxy, proxy admin and the latest implementation..."
	)

	const wallet = new Wallet(process.env.DEPLOYER_PRIVATE_KEY)
	const deployer = new Deployer(hre, wallet)

	const artifact = await deployer.loadArtifact(Contracts.Blog)

	console.log(
		"Initializer arguments: ",
		JSON.stringify(ContractConstructorArguments.TwilightBlog)
	)

	const contracts = await hre.zkUpgrades.deployProxy(
		deployer.zkWallet,
		artifact,
		ContractConstructorArguments.TwilightBlog
	)
	await contracts.deployed()

	console.log(
		`${artifact.contractName} has been deployed, ` +
			"run verify:blog to verify the latest implementation contract"
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
