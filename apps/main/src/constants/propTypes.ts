/* eslint-disable camelcase */
import { tuple } from '~/utilities/dataTypes'

const listTypes = tuple('index', 'cate', 'search')
const labelTypes = tuple('sticky', 'primary', 'secondary', 'green', 'gray')

export type ListTypes = typeof listTypes[number]
export type LabelTypes = typeof labelTypes[number]
