# ouorz-analytics

Personal Analytics Service Powered by [Umami](https://umami.is)

![screenshot](https://user-images.githubusercontent.com/21199796/169626650-888863e5-d767-4ff4-b59d-9b727b166ef3.png)

<br/>

## Website
[https://analytics.ouorz.com →](https://analytics.ouorz.com)

<br/>

## Database
+ [Supabase](https://supabase.com)
+ Type: PostgreSQL
+ Location: Germany (Frankurt)

<br/>

## Hosting
Powered by [Vercel](https://vercel.com)

## Geolocation database

Each build fetches MaxMind's GeoLite2 Country database from the maintained [node-geolite2-redist redistribution](https://github.com/GitSquared/node-geolite2-redist), which automatically publishes database updates. This is the single download source and requires no license-key configuration. The build validates the database before replacing the local file and fails on download or validation errors.

## Server statistics credentials

The main site uses `ANALYTICS_TOKEN` to read analytics statistics. Browser login tokens expire after 24 hours and must not be used for that integration.

From `apps/analytics`, load the same database connection variables and `HASH_SALT` used by the analytics deployment, then run:

```sh
bun run scripts/issue-stats-token.mjs <account-username> <website-id>
```

The command reads the account and website, writes no database records, and prints the credential to stdout. The account must own that website or be an administrator. Prefer an existing non-admin account that owns the intended website. `.env` is loaded automatically; exported variables take precedence.

Set the printed value as the main site's server-only `ANALYTICS_TOKEN`, then restart or redeploy the main site so it reads the new value. The credential permits only `GET /api/website/<website-id>/stats`, including its date and URL filters. It grants no dashboard access, access to another website, or write permissions. Do not put it in a `NEXT_PUBLIC_` variable or a browser.

Issue and install a replacement before its 365-day expiry. Editing the account through the API, changing its password, or deleting it revokes existing service credentials. Account edits advance a persisted revision, so restoring an earlier administrator status does not restore old credentials. Requests also require the account's current access to the website. Changing the analytics signing secret revokes all credentials. Issuing another credential alone does not revoke the previous one.

Use the account API for permission changes. If an operator changes permissions directly in the database, also reset that account's password to invalidate previously issued credentials permanently.

## Database runtime

Runtime requests, operator scripts, schema selection, and Prisma client generation prefer `DATABASE_POOLING_URL`, then `DATABASE_DIRECT_URL`, then `DATABASE_URL`. Supported schemes are `postgresql://`, `postgres://`, and `mysql://`. Keep the provider's required pooling parameters, such as `pgbouncer=true` and `connection_limit`, in the pooled URL. If `DATABASE_TYPE` is set, it must match the selected connection as `postgresql` or `mysql`.

Prisma schema operations, including introspection and migrations, use `DATABASE_DIRECT_URL`, or an unpooled `DATABASE_URL` when it is unset. These commands never select `DATABASE_POOLING_URL`. Use the package scripts or `node scripts/prisma.js <command>` so the correct connection is passed to Prisma. The pooled and unpooled connections must point to the same database.

Run `bun run build` to generate the matching Prisma client with its database engine and build the application. Prisma proxy URLs such as `prisma://` and `prisma+postgres://` are rejected before startup; no hosted Accelerate service or extension is used.

Use a stable `HASH_SALT` for token signing. When it is unset, the selected runtime database URL supplies the signing secret, so adding or changing `DATABASE_POOLING_URL` can invalidate existing credentials. Issue replacement integration credentials after any signing-secret change. The optional database seed requires an explicit `ADMIN_PASSWORD`; it has no default password.
