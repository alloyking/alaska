import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')

    return {
        plugins: [ tailwindcss(), vue() ],

        server: {
            host: true,
            port: 3000,
            proxy: {
                '/api': {
                    target: env.VITE_API_SERVER || 'http://host.docker.internal:8888',
                    changeOrigin: true,
                    secure: false,
                    configure: (proxy, options) => {
                        proxy.on('proxyReq', (proxyReq, req, res) => {
                            console.log(`[vite proxy] ${req.method} ${req.url} → ${proxyReq.path}`)
                        })
                        proxy.on('error', (err, req) => {
                            console.error('[vite proxy error]', err)
                        })
                    },
                },
            },
        },

        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            },
        },
    }
})
