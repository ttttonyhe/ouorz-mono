import * as hre from "hardhat"
import * as Readline from "readline"

import Contracts from "../constants/contracts"

const readline = Readline.createInterface({
	input: process.stdin,
	output: process.stdout,
})

async function main(address: string) {
	if (!address) {
		throw new Error("Please provide the implementation contract address")
	}

	console.log("Verifying Twilight Blog implementation contract...")

	const verificationId = await hre.run("verify:verify", {
		address,
		constructorArguments: [],
		contract: `contracts/${Contracts.Blog}.sol:${Contracts.Blog}`,
	})

	if (!verificationId) {
		throw new Error("Verification failed")
	}
}

readline.question("Enter implementation address: ", (address: string) => {
	main(address).catch((error) => {
		console.error(error)
		process.exitCode = 1
	})
	readline.close()
})
