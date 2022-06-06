import React from 'react'
import { ComponentStory, ComponentMeta } from '@storybook/react'

import Icon from '.'
import icons from './icons'

export default {
	title: 'Components/ Icon',
	component: Icon,
	argTypes: {
		name: {
			options: Object.keys(icons),
			control: { type: 'select' },
		},
	},
} as ComponentMeta<typeof Icon>

const Template: ComponentStory<typeof Icon> = (args) => {
	return (
		<i className="flex w-8 h-8">
			<Icon {...args} />
		</i>
	)
}

export const Default = Template.bind({})
