# Twilight protocol

The blog stores author-controlled metadata and category entry identifiers behind an OpenZeppelin transparent proxy. This is a new EVM deployment. The retired Goerli EraVM deployment is no longer configured, and its storage layout is not an upgrade target.

From this directory, run `bun run build` to compile contracts and export the interface ABI to `dist/TwilightBlog.json`. Hardhat 3, ethers 6, and OpenZeppelin Upgrades validate deployments and storage compatibility. Keep the generated `.openzeppelin` network manifests when managing deployed proxies.

For local development, start `bunx hardhat node` in one terminal. In another, run:

```sh
BLOG_URI='ipfs://your-blog-metadata' bun run deploy:blog --network localhost
```

The script prints the chain ID and confirmed proxy, admin, and implementation addresses. Use the printed proxy address and chain ID in the interface configuration. Local accounts are funded only on the local development chain.

The `zkSyncSepolia` network targets chain 300 with standard EVM bytecode. A remote deployment requires `DEPLOYER_PRIVATE_KEY`, `BLOG_URI`, and an explicit `--network zkSyncSepolia`. No remote deployment is included in this repository. ZKsync documents this tooling path in its [EVM interpreter guide](https://docs.zksync.io/zksync-protocol/era-vm/evm-interpreter/faq).

To upgrade a proxy created by this version, set `BLOG_PROXY_ADDRESS` and run `bun run upgrade:blog --network <network>`. The script uses OpenZeppelin's storage checks and waits for confirmation. To verify it, set `ETHERSCAN_API_KEY` and run `bun run verify:blog --network <network> <proxy-address>`.

Category and entry reads take `start` and `end` offsets with an exclusive end and a maximum page size of 256. Set ordering can change after deletion, so read all pages at the same block. `deleteCategory(id)` removes at most 256 entries per call and returns `false` when more cleanup is needed. The final call clears the membership indexes, deletes the category metadata, returns `true`, and emits `CategoryDeleted`. Recreating the same identifier starts with an empty entry set.
