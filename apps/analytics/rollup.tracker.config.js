import buble from '@rollup/plugin-buble';
import { terser } from 'rollup-plugin-terser';

export default {
  input: 'tracker/index.js',
  output: {
    file: 'public/analytics.js',
    format: 'iife',
  },
  plugins: [buble({ objectAssign: true }), terser({ compress: { evaluate: false } })],
};
