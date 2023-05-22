import * as hre from "hardhat"
import * as Readline from "readline"

const readline = Readline.createInterface({
	input: process.stdin,
	output: process.stdout,
})

async function main(address: string) {
	const implContractAddress = address
	if (!implContractAddress) {
		throw new Error("Please provide the implementation contract address")
	}

	console.log(`Verifying Blog contract implementation...`)

	const verificationId = await hre.run("verify:verify", {
		address: implContractAddress,
		constructorArguments: [],
		contract: "contracts/Blog.sol:Blog",
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
