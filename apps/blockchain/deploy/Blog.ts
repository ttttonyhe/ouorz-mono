import { Wallet } from "zksync-web3"
import * as ethers from "ethers"
import { HardhatRuntimeEnvironment } from "hardhat/types"
import { Deployer } from "@matterlabs/hardhat-zksync-deploy"

// An example of a deploy script that will deploy and call a simple contract.
export default async function (hre: HardhatRuntimeEnvironment) {
	console.log(`Running deploy script for the Blog contract`)

	// Initialize the wallet.
	const wallet = new Wallet(process.env.DEPLOYER_PRIVATE_KEY)

	// Create deployer object and load the artifact of the contract you want to deploy.
	const deployer = new Deployer(hre, wallet)
	const artifact = await deployer.loadArtifact("Blog")

	// Estimate contract deployment fee
	const title = "Test Blog"
	const description = "This is a test blog"
	const args = [title, description]

	const deploymentFee = await deployer.estimateDeployFee(artifact, args)

	// Deploy this contract. The returned object will be of a `Contract` type, similarly to ones in `ethers`.
	const parsedFee = ethers.utils.formatEther(deploymentFee.toString())
	console.log(
		`The deployment is estimated to cost ${parsedFee} ETH, deploying...`
	)
	//obtain the Constructor Arguments
	console.log("Constructor args:", JSON.stringify(args))

	const contract = await deployer.deploy(artifact, args)

	// Show the contract info.
	const contractAddress = contract.address
	console.log(
		`${artifact.contractName} has been deployed to ${contractAddress}`
	)

	// Verify the contract
	console.log("Verifying the contract...")
	const verificationId = await hre.run("verify:verify", {
		address: contractAddress,
		constructorArguments: args,
	})

	if (!verificationId) {
		throw new Error("Verification failed")
	}

	console.log("Verification id:", verificationId)
}
