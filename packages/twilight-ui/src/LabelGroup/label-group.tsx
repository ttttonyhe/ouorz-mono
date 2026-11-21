import type React from "react"

import Label from "../Label/label"

interface Props {
	children?: React.ReactNode
}

type NativeAttrs = Omit<React.HTMLAttributes<HTMLDivElement>, keyof Props>
export type LabelGroupProps = Props & NativeAttrs

const defaultChildren = (
	<>
		<Label type="primary" icon="sticky" />
		<Label type="gray-icon" icon="preview" />
	</>
)

const LabelGroup = ({
	children = defaultChildren,
	className,
	...rest
}: LabelGroupProps) => (
	<div {...rest} className={`label-group ${className ?? ""}`}>
		{children}
	</div>
)

LabelGroup.displayName = "LabelGroup"

export default LabelGroup
