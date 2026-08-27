// Plugin load order is significant: zksync-verify must override the Etherscan
// verify tasks before zksync-upgradable pulls in the OpenZeppelin upgrades plugin.
import "@nomicfoundation/hardhat-ethers"
import "@matterlabs/hardhat-zksync-deploy"
import "@matterlabs/hardhat-zksync-solc"
import "@matterlabs/hardhat-zksync-verify"
import "@matterlabs/hardhat-zksync-upgradable"
import * as dotenv from "dotenv"
import type { HardhatUserConfig } from "hardhat/types"

dotenv.config()

const config: HardhatUserConfig = {
	solidity: {
		version: "0.8.30",
		settings: {
			// solc 0.8.30 defaults to Prague; the zkVM toolchain tops out at Cancun.
			evmVersion: "cancun",
		},
	},
	zksolc: {
		version: "1.5.17",
		compilerSource: "binary",
		settings: {
			// zksolc defaults to Yul; pin it so a future default change cannot alter bytecode.
			codegen: "yul",
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
}

export default config
