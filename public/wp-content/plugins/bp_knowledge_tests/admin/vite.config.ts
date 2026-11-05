import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const deps = path.resolve(__dirname, '../../../../vendor/node_modules');

const { defineConfig } = await import(pathToFileURL(path.join(deps, 'vite/dist/node/index.js')).href);
const reactPlugin = await import(pathToFileURL(path.join(deps, '@vitejs/plugin-react/dist/index.js')).href);

export default defineConfig({
  plugins: [reactPlugin.default()],
  resolve: {
    alias: [
      { find: 'react-dom/client', replacement: path.join(deps, 'react-dom/client') },
      { find: 'react/jsx-dev-runtime', replacement: path.join(deps, 'react/jsx-dev-runtime') },
      { find: 'react/jsx-runtime', replacement: path.join(deps, 'react/jsx-runtime') },
      { find: 'react-dom', replacement: path.join(deps, 'react-dom') },
      { find: 'react', replacement: path.join(deps, 'react') },
    ],
  },
  build: {
    outDir: path.resolve(__dirname, '../assets/admin'),
    emptyOutDir: false,
    rollupOptions: {
      input: path.resolve(__dirname, 'src/main.tsx'),
      output: {
        format: 'iife',
        entryFileNames: 'app.js',
        chunkFileNames: 'chunk-[name].js',
        assetFileNames: 'app.[ext]',
        inlineDynamicImports: true,
      },
    },
  },
});
