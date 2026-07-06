import { defineConfig, globalIgnores } from "eslint/config";
import nextVitals from "eslint-config-next/core-web-vitals";
import nextTs from "eslint-config-next/typescript";

const eslintConfig = defineConfig([
  ...nextVitals,
  ...nextTs,
  // Override default ignores of eslint-config-next.
  globalIgnores([
    // Default ignores of eslint-config-next:
    ".next/**",
    "out/**",
    "build/**",
    "next-env.d.ts",
  ]),
  {
    rules: {
      // We intentionally load data / read browser APIs on mount and setState
      // after the async completes — the idiomatic client-fetch pattern. This
      // new rule flags that as if it were a synchronous cascade, so keep it
      // advisory rather than build-breaking.
      "react-hooks/set-state-in-effect": "warn",
      // Allow `cond ? a() : b()` used purely for its side effect (set toggles).
      "@typescript-eslint/no-unused-expressions": "warn",
    },
  },
]);

export default eslintConfig;
