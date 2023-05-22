import { Deployer } from "@matterlabs/hardhat-zksync-deploy"
import { Wallet } from "zksync-web3"

import * as hre from "hardhat"

async function main() {
	console.log(`Running upgrade script for the Blog contract`)

	// Initialize the wallet
	const wallet = new Wallet(process.env.DEPLOYER_PRIVATE_KEY)

	// Create deployer object and load the artifact of the contract you want to deploy
	const deployer = new Deployer(hre, wallet)
	const artifact = await deployer.loadArtifact("Blog")

	// Obtain the Constructor Arguments
	const args = ["uri"]
	console.log("Initialize args:", JSON.stringify(args))

	// Deploy the contract
	const contract = await hre.zkUpgrades.upgradeProxy(
		deployer.zkWallet,
		"0x2a8D52eB4ba8eA5108c39222824b83719aAae47B",
		artifact
	)
	await contract.deployed()

	console.log(
		`${artifact.contractName} has been upgraded, run verify:blog to verify the implementation contract`
	)
}

main().catch((error) => {
	console.error(error)
	process.exitCode = 1
})
