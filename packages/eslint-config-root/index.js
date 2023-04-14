module.exports = {
  // Disable all rules that are unnecessary or might conflict with Prettier
  extends: ["turbo", "prettier"],
  rules: {
    // Ensure all detectable usage of environment variables are correctly included in Turborepo config's cache keys
    "turbo/no-undeclared-env-vars": "error"
  },
};
