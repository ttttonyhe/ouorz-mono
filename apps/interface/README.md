# Twilight interface

This is a wallet-connected protocol demo. Its six buttons connect a wallet, read the blog URI, read the author, read categories, update the URI to `newUri`, and create a sample category using the next free identifier starting at 2. There are no authoring inputs.

Set these values in `.env.local` using an actual deployment:

```dotenv
NEXT_PUBLIC_BLOG_CHAIN_ID=31337
NEXT_PUBLIC_BLOG_ADDRESS=<proxy address printed by deploy:blog>
```

Build the protocol ABI first with `bun run --cwd ../../packages/twilight-protocol build`, then run `bun run dev`. The workspace build also handles this dependency. The interface reads and signs through the same injected wallet provider. It checks the configured chain and deployed code before connecting, checks the author before writes, and refreshes data after a confirmed transaction.

The default protocol development node uses chain 31337 at `http://127.0.0.1:8545`. Configure that network in the wallet and import a local development account only for local testing. Public deployment requires the corresponding chain ID and proxy address. The retired Goerli addresses are no longer used.
