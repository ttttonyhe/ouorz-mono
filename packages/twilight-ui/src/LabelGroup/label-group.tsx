import React from "react"
import Label from "../Label/label"

interface Props {
	/**
	 * The content inside the group
	 */
	children?: React.ReactNode
}

type NativeAttrs = Omit<React.HTMLAttributes<any>, keyof Props>
export type LabelGroupProps = Props & NativeAttrs

const LabelGroup = ({
	children = (
		<>
			<Label type="primary" icon="sticky" />
			<Label type="gray-icon" icon="preview" />
		</>
	),
	className,
}: LabelGroupProps) => {
	return (
		<div className={`label-group ${className ?? ""}`}>
			<>{children}</>
		</div>
	)
}

LabelGroup.displayName = "LabelGroup"

export default LabelGroup
