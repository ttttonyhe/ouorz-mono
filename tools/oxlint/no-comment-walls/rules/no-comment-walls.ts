import { defineRule } from "@oxlint/plugins";

const DEFAULT_MAX = 2;

/** Ban blocks of consecutive line comments; prose belongs in docs, not comment walls. */
export const noCommentWallsRule = defineRule({
	meta: {
		type: "suggestion",
		docs: {
			description:
				"Disallow blocks of consecutive line comments (comment walls); state the constraint in a line or two, or move the essay to a doc.",
		},
		messages: {
			commentWall:
				"Comment wall: {{count}} consecutive line comments (max {{max}}). Cut it to {{max}}, write clearer code that needs no explanation, or move the essay to a doc.",
		},
		schema: [
			{
				type: "object",
				properties: {
					max: { type: "integer", minimum: 1 },
				},
				additionalProperties: false,
			},
		],
	},
	create(context) {
		const options = context.options[0] as { max?: number } | undefined;
		const max = options?.max ?? DEFAULT_MAX;
		return {
			Program() {
				const comments = context.sourceCode.getAllComments();
				let run: (typeof comments)[number][] = [];
				const flush = () => {
					if (run.length > max) {
						context.report({
							loc: run[0].loc,
							messageId: "commentWall",
							data: { count: String(run.length), max: String(max) },
						});
					}
					run = [];
				};
				for (const comment of comments) {
					if (comment.type !== "Line") {
						flush();
						continue;
					}
					const previous = run[run.length - 1];
					if (previous && comment.loc.start.line === previous.loc.end.line + 1) {
						run.push(comment);
					} else {
						flush();
						run.push(comment);
					}
				}
				flush();
			},
		};
	},
});
