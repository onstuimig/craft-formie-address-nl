import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
	build: {
		lib: {
			entry: resolve(__dirname, 'src/js/autocomplete.ts'),
			formats: ['es'],
			fileName: (format, entryName) => `${entryName}.js`,
		},
		outDir: 'dist',
		emptyOutDir: true,
	},
})
