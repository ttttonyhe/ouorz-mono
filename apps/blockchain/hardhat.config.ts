import { HardhatUserConfig } from "hardhat/types"
import * as dotenv from "dotenv"

import "@matterlabs/hardhat-zksync-deploy"
import "@matterlabs/hardhat-zksync-solc"
import "@matterlabs/hardhat-zksync-verify"
import "@matterlabs/hardhat-zksync-upgradable"

dotenv.config()

module.exports = {
	zksolc: {
		version: "1.3.10",
		compilerSource: "binary",
		settings: {
			optimizer: {
				enabled: true,
				mode: "3",
			},
		},
	},
	defaultNetwork: "zkSyncTestnet",
	networks: {
		zkSyncTestnet: {
			url: "https://testnet.era.zksync.dev",
			verifyURL:
				"https://zksync2-testnet-explorer.zksync.dev/contract_verification",
			ethNetwork: "goerli",
			zksync: true,
		},
	},
	solidity: {
		version: "0.8.18",
	},
} as HardhatUserConfig
