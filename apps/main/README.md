# ouorz-main

Front-end code that powers [lipeng.ac](https://lipeng.ac).

![screenshot](https://static.ouorz.com/screen-shot-ouorz-next.png)

## Website

[https://lipeng.ac →](https://lipeng.ac)

## Hosting

Powered by [Vercel](https://vercel.com).

## Content system

This app now uses a local, fully static content workflow:

- Posts: `content/posts/*.mdx`
- Structured data: `content/data/*.json`
- Content loader: `src/content/*`
- Content API: `src/pages/api/content/[resource].ts`

## Runtime services

Set server credentials in `apps/main/.env.local` for local development and in
the hosting project's environment for deployment. Never use `NEXT_PUBLIC_`
for server credentials.

| Service                | Server variables                                                                                        |
| ---------------------- | ------------------------------------------------------------------------------------------------------- |
| Analytics              | `ANALYTICS_TOKEN`, a website-scoped service credential from [the analytics app](../analytics/README.md) |
| Ethereum NFTs          | `ALCHEMY_API_PATH`, including the NFT API credential                                                    |
| Solana NFTs            | `QUICK_NODE_API_PATH`, with `qn_fetchNFTs` enabled                                                      |
| GitHub statistics      | `GITHUB_TOKEN`                                                                                          |
| Twitter statistics     | `TWITTER_BEARER_TOKEN`                                                                                  |
| Article summaries      | `OPENAI_API_KEY`                                                                                        |
| Server-triggered email | `RESEND_API_KEY`, `RESEND_REQUEST_TOKEN`, optional `RESEND_FROM_ADDRESS`                                |
| Content revalidation   | `REVALIDATION_REQUEST_TOKEN`                                                                            |
| Error reporting        | Optional `NEXT_PUBLIC_SENTRY_DSN`                                                                       |

The email endpoint accepts authenticated server requests. Browser clients must
never receive `RESEND_REQUEST_TOKEN`. Reply notifications still need a trusted
comment-storage integration; enabling the Nexment checkbox alone does not send
notifications. Likes return HTTP 501 until a durable store is configured.

Provider failures return non-success responses. Secret-bearing provider URLs
are excluded from Sentry HTTP spans and breadcrumbs.

### Recovering historical posts

If WordPress is unavailable and you need to recover old posts from public archives, run:

```bash
bun run content:recover:wayback
```

This script pulls snapshots from the Wayback Machine and writes starter MDX files under `content/posts` for manual cleanup.
