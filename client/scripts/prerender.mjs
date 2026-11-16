import { createServer } from 'node:http'
import { existsSync, mkdirSync, readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs'
import { dirname, extname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import { chromium } from 'playwright'

const clientRoot = dirname(dirname(fileURLToPath(import.meta.url)))
const dist = resolve(clientRoot, 'dist')
const routes = ['/', '/catalogue', '/contribuer']
for (const file of readdirSync(resolve(clientRoot, '../data/guides'))) {
  if (file.endsWith('.json')) {
    const guide = JSON.parse(readFileSync(resolve(clientRoot, '../data/guides', file), 'utf8'))
    routes.push(`/produit/${guide.source}`, `/produit/${guide.source}/imprimer`)
  }
}
const mimeTypes = {
  '.css': 'text/css; charset=utf-8',
  '.html': 'text/html; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.svg': 'image/svg+xml',
  '.txt': 'text/plain; charset=utf-8',
  '.xml': 'application/xml; charset=utf-8',
  '.yaml': 'application/yaml; charset=utf-8',
}

function filePath(urlPath) {
  const safePath = decodeURIComponent(urlPath).replace(/\.\./g, '')
  const candidate = resolve(dist, `.${safePath}`)
  if (existsSync(candidate) && statSync(candidate).isFile()) return candidate
  const index = resolve(candidate, 'index.html')
  return existsSync(index) ? index : null
}

const server = createServer((request, response) => {
  const pathname = new URL(request.url, 'http://localhost').pathname
  const path = filePath(pathname) || resolve(dist, 'index.html')
  const body = readFileSync(path)
  response.writeHead(200, { 'Content-Type': mimeTypes[extname(path)] || 'application/octet-stream' })
  response.end(body)
})
await new Promise((resolve) => server.listen(4173, '127.0.0.1', resolve))

const executablePath = process.env.CHROMIUM_PATH || ['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'].find((path) => existsSync(path))
const browser = await chromium.launch({
  headless: true,
  executablePath,
  args: ['--no-sandbox', '--disable-gpu'],
})

try {
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } })
  for (const route of routes) {
    await page.goto(`http://127.0.0.1:4173${route}`, { waitUntil: 'networkidle' })
    await page.waitForTimeout(300)
    const html = await page.content()
    const destination = route === '/' ? resolve(dist, 'index.html') : resolve(dist, route.slice(1), 'index.html')
    mkdirSync(dirname(destination), { recursive: true })
    writeFileSync(destination, html)
    console.log(`Pré-rendu : ${route}`)
  }
} finally {
  await browser.close()
  server.close()
}
