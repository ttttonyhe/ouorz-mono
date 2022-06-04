<div align="center">
  <h1>OUORZ-MONO</h1>
  <p>Front-end codes that power <a href="https://www.ouorz.com" target="_blank">www.ouorz.com</a> and other related projects</p>

  <a href="https://github.com/HelipengTony/ouorz-mono">
    <img src="https://github.com/HelipengTony/ouorz-mono/workflows/Tests/badge.svg?branch=main" alt="build status">
  </a>

  <a href="https://codeclimate.com/github/HelipengTony/ouorz-mono/maintainability">
    <img src="https://api.codeclimate.com/v1/badges/241ce6373b176b12f511/maintainability" alt="maintainability" />
  </a>

  <a href="https://github.com/HelipengTony/ouorz-mono">
    <img src="https://img.shields.io/github/license/HelipengTony/ouorz-mono.svg" alt="license">
  </a>
</div>

<br/>

![Thumbnail_index](https://static.ouorz.com/screen-shot-ouorz-next.png)

<br/>
<hr/>

## Main App
### Past Iterations
- Antony-Nuxt (Nuxt.js / Vue.js v2) [https://github.com/HelipengTony/antony-nuxt](https://github.com/HelipengTony/antony-nuxt)
- Antony (Vue.js v2) [https://github.com/HelipengTony/antony](https://github.com/HelipengTony/antony)
- Tony (Vue.js v2 / PHP) [https://github.com/HelipengTony/tony](https://github.com/HelipengTony/tony)

<br/>

### Feature Highlights

+ SSR/SSG/On-demand ISR
+ Command palette
+ Redux + Redux Saga state management
+ Nexment comment system
+ Dynamic table of contents sidebar
+ Personal dashboard
+ Dark mode

<br/>

### Summary of Tech Stacks
Super opinionated, completely overkill.

+ TypeScript
+ React.js / Next.js
  - `next/image`
  - `next-themes`
+ SWR
  - Infinite Loading
  - Revalidation
+ Redux
  - Redux Toolkit
  - Redux Saga
+ Tailwind CSS
+ WordPress
  - WordPress REST API
+ Error Logging
  - Sentry

<br/>

### Project Setup
```bash
git clone git@github.com:HelipengTony/ouorz-mono.git
yarn install
cd apps/main
```

Create a `.env` file with your configuration, see below for a list of environment variables used in this project:

+ LeanCloud:
  - NEXT_PUBLIC_LC_KEY
  - NEXT_PUBLIC_LC_ID
+ Buttondown:
  - NEXT_PUBLIC_BUTTONDOWN_TOKEN
+ Sentry:
  - NEXT_PUBLIC_SENTRY_DSN
  - SENTRY_AUTH_TOKEN
  - SENTRY_PROJECT
  - SENTRY_ORG
+ On-demand ISR:
  - REVALIDATION_REQUEST_TOKEN

```
yarn run dev
```

<br/>

### WordPress REST API configurations
> **Warning**:
> May not be up-to-date all the time

Copy the codes in `apps/main/src/assets/scripts/vendors/wordpress/functions.php` and add them to your WordPress theme's `functions.php`

<br/>
<hr/>

## Analytics App
See [apps/analytics/README.md](https://github.com/HelipengTony/ouorz-mono/tree/main/apps/analytics#readme) for more details

<br/>

### Project Setup
```bash
git clone git@github.com:HelipengTony/ouorz-mono.git
yarn install
cd apps/analytics
```

Create a `.env` file with your configuration, see below for a list of environment variables used in this project:

+ DATABASE_URL
+ HASH_SALT
+ MAXMIND_LICENSE_KEY

```
yarn run build-postgresql-client
yarn run dev
```

<br/>
<hr/>

## Twilight Toolkit
A super opinionated front-end toolkit library

- @twilight-toolkit/ui
- @twilight-toolkit/utils

<br/>
<hr/>

## Monorepo
Monorepo build system: Turborepo

<br/>

## Development
### Gitflow
Not really following this though...

![git_branching_workflow](https://user-images.githubusercontent.com/21199796/135544887-50b1e78b-aa72-4e98-8f08-baac092cf393.jpg)

<br/>

### E2E Testing
Start server:
```bash
yarn run build:main
yarn run start:main

# or
cd apps/main
yarn run dev:test
```

Run tests:
```bash
yarn run test:main
```

`apps/main` uses Cypress Dashboard, disable it by changing the configuration file accordingly.

<br/>

## Deployment
### apps/main
This project utilizes a combination of Server-side Rendering (SSR) and (On-demand) Incremental Static Generation (ISG):

```bash
yarn run build:main
yarn run start:main
```

<br/>

### apps/analytics
See [apps/analytics/README.md](https://github.com/HelipengTony/ouorz-mono/tree/main/apps/analytics#readme) for more details.

```bash
yarn run build:analytics
yarn run start:analytics
```

<br/>

### Deploy with Vercel / Netlify
Make sure to set root directory path to `apps/<project-name>`

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/HelipengTony/ouorz-mono)

[![Deploy with Netlify](https://www.netlify.com/img/deploy/button.svg)](https://app.netlify.com/start/deploy?repository=https://github.com/HelipengTony/ouorz-mono)
