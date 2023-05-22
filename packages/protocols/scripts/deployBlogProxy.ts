import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import { Wallet } from "zksync-web3"

import * as hre from "hardhat"

async function main() {
	console.log(`Running deploy script for the Blog contract`)

	// Initialize the wallet
	const wallet = new Wallet(process.env.DEPLOYER_PRIVATE_KEY)

	// Create deployer object and load the artifact of the contract you want to deploy
	const deployer = new Deployer(hre, wallet)
	const artifact = await deployer.loadArtifact("Blog")

	// Obtain the Constructor Arguments
	const args = ["uri"]
	console.log("Constructor args:", JSON.stringify(args))

	// Deploy the contract
	const contract = await hre.zkUpgrades.deployProxy(
		deployer.zkWallet,
		artifact,
		args
	)
	await contract.deployed()

	// Show the contract info
	console.log(
		`${artifact.contractName} has been deployed, run verify:blog to verify the latest implemenation contract`
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
