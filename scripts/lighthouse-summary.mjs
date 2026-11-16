import { readFileSync } from 'node:fs'

let failed = false
for (const file of process.argv.slice(2)) {
  const report = JSON.parse(readFileSync(file, 'utf8'))
  console.log(`\n${file}`)
  for (const [key, category] of Object.entries(report.categories)) {
    const score = Math.round(category.score * 100)
    console.log(`${key}: ${score}/100`)
    if (['accessibility', 'best-practices', 'seo'].includes(key) && score < 100) failed = true
  }
}
if (failed) process.exitCode = 1
