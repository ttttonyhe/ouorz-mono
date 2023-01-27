# `ouorz-mono` Packages

<br/>

## Twilight Toolkit

- @twilight-toolkit/ui
- @twilight-toolkit/utils

<br/>

### Use them in internal Apps

To use Twilight Toolkit inside ouorz monorepo Next.js apps, follow these steps:

1. Add Twilight Toolkit packages to the app's dependencies in `package.json`, for example:

   ```json
   {
   	"dependencies": {
   		"@twilight-toolkit/ui": "*"
   	}
   }
   ```

2. Add path aliases for Twilight Toolkit packages in `tsconfig.json` so that they can be imported directly without building, for example:
   ```json
   {
   	"paths": {
   		"@twilight-toolkit/ui": ["../../packages/twilight-ui/index"]
   	}
   }
   ```

<details>
    <summary>Optional Steps</summary>

<br/>

The following steps are now optional as Next.js introduced [built-in support](https://beta.nextjs.org/docs/api-reference/next.config.js#transpilepackages) for external module transpilation:

- Transpile Twilight Toolkit packages using `next-transpile-modules` library, add the following to `next.config.js` for example:

  ```javascript
  const withTM = require("next-transpile-modules")(["@twilight-toolkit/ui"]);
  module.exports = withTM({});
  ```

- Twilight Toolkit UI relies on Tailwind CSS for component styling, add the path to the components to the `content` of Tailwind CSS configuration:

  ```javascript
  module.exports = {
      content: ["../../packages/twilight-ui/**/*.tsx"],
  };
  ```

</details>

<br/>

### Use them as a standalone libraries

Twilight Toolkit will be published as a standalone library on NPM, ETA to be determined.
