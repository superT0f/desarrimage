import { copyFileSync, existsSync, mkdirSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const clientRoot = dirname(dirname(fileURLToPath(import.meta.url)))
const publicRoot = resolve(clientRoot, 'public')
const assetsRoot = resolve(publicRoot, 'swagger-assets')
mkdirSync(assetsRoot, { recursive: true })

for (const file of ['swagger-ui.css', 'swagger-ui-bundle.js', 'swagger-ui-standalone-preset.js']) {
  copyFileSync(resolve(clientRoot, 'node_modules/swagger-ui-dist', file), resolve(assetsRoot, file))
}
const specPath = [
  resolve(clientRoot, '../api/openapi.yaml'),
  resolve(clientRoot, 'spec/openapi.yaml'),
].find((candidate) => existsSync(candidate))
if (!specPath) throw new Error('Spécification OpenAPI introuvable.')
copyFileSync(specPath, resolve(publicRoot, 'openapi.yaml'))
console.log('Documentation API synchronisée (Swagger UI local).')
