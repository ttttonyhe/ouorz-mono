import hardhatEthers from "@nomicfoundation/hardhat-ethers"
import hardhatVerify from "@nomicfoundation/hardhat-verify"
import hardhatUpgrades from "@openzeppelin/hardhat-upgrades"
import "dotenv/config"
import { configVariable, defineConfig } from "hardhat/config"

export default defineConfig({
	plugins: [hardhatEthers, hardhatVerify, hardhatUpgrades],
	solidity: {
		version: "0.8.30",
		settings: {
			evmVersion: "cancun",
			optimizer: { enabled: true, runs: 200 },
		},
	},
	networks: {
		default: { type: "edr-simulated", chainId: 31337 },
		localhost: {
			type: "http",
			url: "http://127.0.0.1:8545",
			chainId: 31337,
		},
		zkSyncSepolia: {
			type: "http",
			url: "https://sepolia.era.zksync.dev",
			chainId: 300,
			accounts: [configVariable("DEPLOYER_PRIVATE_KEY")],
		},
	},
	verify: {
		etherscan: { apiKey: configVariable("ETHERSCAN_API_KEY") },
	},
})
