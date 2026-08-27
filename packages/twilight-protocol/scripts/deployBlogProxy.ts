import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import * as hre from "hardhat"
import { Wallet } from "zksync-ethers"

import ContractConstructorArguments from "../constants/arguments"
import Contracts from "../constants/contracts"
import { requireDeployerPrivateKey } from "./deployerWallet"

async function main() {
	console.log(
		"Deploying Twilight Blog proxy, proxy admin and the latest implementation..."
	)

	const wallet = new Wallet(requireDeployerPrivateKey())
	const deployer = new Deployer(hre, wallet)

	const artifact = await deployer.loadArtifact(Contracts.Blog)

	console.log(
		"Initializer arguments: ",
		JSON.stringify(ContractConstructorArguments.TwilightBlog)
	)

	const contract = await hre.zkUpgrades.deployProxy(
		deployer.zkWallet,
		artifact,
		ContractConstructorArguments.TwilightBlog
	)
	await contract.waitForDeployment()

	console.log(
		`${artifact.contractName} has been deployed, ` +
			"run verify:blog to verify the latest implementation contract"
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
