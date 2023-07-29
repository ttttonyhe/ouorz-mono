# Twilight UI

A super opinionated UI library for React & Tailwind CSS

![twilight-toolkit-storybook](https://user-images.githubusercontent.com/21199796/182478030-52acb1f1-c60d-415b-9924-195e9b9d2ca5.png)

<br/>

## Foreword
This project is work in progress.

<br/>

## Storybook

[https://ui.twilight-toolkit.ouorz.com →](https://ui.twilight-toolkit.ouorz.com)

<br/>

## Default usage (Standalone)

1. Import the Twilight UI stylesheet (with Tailwind CSS included in the bundle) globally:

   ```react
   import "./node_module/@twilight-toolkit/ui/dist/index.css"
   ```

2. Import components from `@twilight-toolkit/ui`:

   ```react
   import { Button } from "@twilight-toolkit/ui"
   ```

<br/>

## Headless usage

In a Tailwind CSS project, Twilight UI should be used headlessly (to avoid styling conflicts).

1. Import the Twilight UI stylesheet (without Tailwind CSS included in the bundle) globally:

   ```react
   import "./node_module/@twilight-toolkit/ui/dist/index-headless.css"
   ```

2. Configure `tailwind.config.js` for Tailwind CSS to compile Twilight UI's styling:

   ```javascript
   module.exports = {
     content: [
      // ...
      "./node_module/@twilight-toolkit/ui/dist/index-headless.js",
     ],
   }
   ```

3. Import components from `@twilight-toolkit/ui`:

   ```react
   import { Button } from "@twilight-toolkit/ui"
   ```
