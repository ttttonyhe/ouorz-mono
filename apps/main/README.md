# ouorz-main

Front-end code that powers [lipeng.ac](https://lipeng.ac).

![screenshot](https://static.ouorz.com/screen-shot-ouorz-next.png)

## Website

[https://lipeng.ac →](https://lipeng.ac)

## Hosting

Powered by [Vercel](https://vercel.com).

## Content System (MDX + JSON)

This app now uses a local, fully static content workflow:

- Posts: `content/posts/*.mdx`
- Structured data: `content/data/*.json`
- Content loader: `src/content/*`
- Runtime API compatibility layer: `src/pages/api/content/[resource].ts`

### Recovering historical posts

If WordPress is unavailable and you need to recover old posts from public archives, run:

```bash
pnpm content:recover:wayback
```

This script pulls snapshots from the Wayback Machine and writes starter MDX files under `content/posts` for manual cleanup.
