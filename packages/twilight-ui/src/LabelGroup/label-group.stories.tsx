import React from 'react'
import { ComponentStory, ComponentMeta } from '@storybook/react'

import LabelGroup from './label-group'
import Label from '../Label/label'

export default {
	title: 'Components/ Label Group',
	component: LabelGroup,
} as ComponentMeta<typeof LabelGroup>

const Template: ComponentStory<typeof LabelGroup> = ({ children }) => {
	return (
		<div className="flex h-[32px] text-label">
			<LabelGroup>{children}</LabelGroup>
		</div>
	)
}

export const OneLabel = Template.bind({})
OneLabel.args = {
	children: (
		<>
			<Label type="green">Preview</Label>
		</>
	),
}

export const TwoLabels = Template.bind({})
TwoLabels.args = {
	children: (
		<>
			<Label type="green" icon="right" preview>
				Preview
			</Label>
			<Label type="gray-icon" icon="preview">
				Gray
			</Label>
		</>
	),
}

export const ThreeLabels = Template.bind({})
ThreeLabels.args = {
	children: (
		<>
			<Label type="gray-icon" icon="preview" />
			<Label type="green" icon="right" preview>
				Preview
			</Label>
			<Label type="sticky-icon" />
		</>
	),
}
