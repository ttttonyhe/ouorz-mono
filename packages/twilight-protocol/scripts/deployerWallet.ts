export const requireDeployerPrivateKey = (): string => {
	const privateKey = process.env.DEPLOYER_PRIVATE_KEY
	if (!privateKey) {
		throw new Error("DEPLOYER_PRIVATE_KEY is not set")
	}
	return privateKey
}
