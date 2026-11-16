import { mkdirSync, readFileSync, readdirSync, writeFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const clientRoot = dirname(dirname(fileURLToPath(import.meta.url)))
const origin = process.env.API_MIRROR_ORIGIN || 'https://desarrimage.prigent.tech'
const mirrorRoot = resolve(clientRoot, 'public/mirror/api')
const endpoints = [
  ['/stats', 'stats'],
  ['/categories', 'categories'],
  ['/products?region=other', 'products.json'],
  ['/guides', 'guides/index'],
]
const guideFiles = readdirSync(resolve(clientRoot, '../data/guides')).filter((file) => file.endsWith('.json'))
for (const file of guideFiles) {
  const guide = JSON.parse(readFileSync(resolve(clientRoot, '../data/guides', file), 'utf8'))
  endpoints.push([`/products/${guide.source}`, `products/${guide.source}`])
  endpoints.push([`/guides/${guide.slug}`, `guides/${guide.slug}`])
}

for (const [endpoint, destination] of endpoints) {
  const response = await fetch(`${origin}/api${endpoint}`)
  if (!response.ok) throw new Error(`${endpoint} : HTTP ${response.status}`)
  const body = await response.text()
  JSON.parse(body)
  const path = resolve(mirrorRoot, destination)
  mkdirSync(dirname(path), { recursive: true })
  writeFileSync(path, body)
  console.log(`${endpoint} → mirror/api/${destination}`)
}
