import { eslintCompatPlugin } from "@oxlint/plugins";

import { noCommentWallsRule } from "./rules/no-comment-walls.ts";

/** Oxlint rule that rejects blocks of consecutive line comments. */
const noCommentWallsPlugin = eslintCompatPlugin({
	meta: { name: "no-comment-walls" },
	rules: {
		"no-comment-walls": noCommentWallsRule,
	},
});

export default noCommentWallsPlugin;
