<script setup>
const props = defineProps({
  guide: { type: Object, required: true },
})

function downloadUrl(url, filename) {
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
}

function downloadJson() {
  const url = `data:application/json;charset=utf-8,${encodeURIComponent(JSON.stringify(props.guide, null, 2))}`
  downloadUrl(url, `desarrimage-${props.guide.slug}.json`)
}

const escapeXml = (value) => String(value).replace(/[<>&'"]/g, (character) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;' }[character]))
const money = (cents) => `${Math.round(cents / 100).toLocaleString('fr-FR')} €`

function downloadPng() {
  const costs = [...props.guide.costs].sort((a, b) => a.year_one_total_cents - b.year_one_total_cents)
  const maximum = Math.max(...costs.map((cost) => cost.year_one_total_cents))
  const bars = costs.map((cost, index) => {
    const width = Math.max(120, Math.round((cost.year_one_total_cents / maximum) * 720))
    const y = 300 + index * 82
    return `<text x="70" y="${y + 28}" fill="#b8cac6" font-size="22">${escapeXml(cost.target.name)}</text><rect x="260" y="${y}" width="${width}" height="42" rx="2" fill="${index === 0 ? '#ed8b72' : '#6ba9a0'}"/><text x="${280 + width}" y="${y + 29}" fill="#fffdf8" font-size="22" font-weight="bold" text-anchor="end">${money(cost.year_one_total_cents)}</text>`
  }).join('')
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="#102f32"/><path d="M70 70h28" stroke="#ed7353" stroke-width="4"/><text x="115" y="78" fill="#79c4b8" font-family="Arial,sans-serif" font-size="18" font-weight="bold" letter-spacing="3">DÉSARRIMAGE · MIGRATION SHARE PACK</text><text x="70" y="165" fill="#fffdf8" font-family="Arial,sans-serif" font-size="46" font-weight="bold">${escapeXml(props.guide.source.name)} : le coût de la traversée</text><text x="70" y="220" fill="#b8cac6" font-family="Arial,sans-serif" font-size="25">${props.guide.scenario.users} utilisateurs · ${props.guide.scenario.data_gb} Go · ${money(props.guide.scenario.day_rate_cents)}/jour-homme</text>${bars}<text x="70" y="570" fill="#b8cac6" font-family="Arial,sans-serif" font-size="19">Année 1 : récurrent + migration + exploitation · ${escapeXml(props.guide.scenario.tax_label)}</text><text x="70" y="605" fill="#79c4b8" font-family="Arial,sans-serif" font-size="18">desarrimage.prigent.tech${escapeXml(props.guide.source.slug === 'google-drive' ? '/produit/google-drive' : `/produit/${props.guide.source.slug}`)}</text></svg>`
  const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const image = new Image()
  image.onload = () => {
    const canvas = document.createElement('canvas')
    canvas.width = 1200
    canvas.height = 630
    canvas.getContext('2d').drawImage(image, 0, 0)
    const png = canvas.toDataURL('image/png')
    downloadUrl(png, `desarrimage-${props.guide.slug}-couts.png`)
    URL.revokeObjectURL(url)
  }
  image.onerror = () => URL.revokeObjectURL(url)
  image.src = url
}
</script>

<template>
  <section class="share-pack" aria-label="Migration Share Pack">
    <div>
      <span class="share-kicker">Migration Share Pack</span>
      <strong>Emportez la traversée avec vous.</strong>
      <small>Sources et hypothèses incluses. Aucun compte, aucun tracking.</small>
    </div>
    <div class="share-actions">
      <button type="button" @click="downloadJson"><span aria-hidden="true">{ }</span> JSON</button>
      <button type="button" @click="downloadPng"><span aria-hidden="true">▧</span> Carte PNG</button>
      <RouterLink :to="`/produit/${guide.source.slug}/imprimer`"><span aria-hidden="true">⎙</span> PDF / A4</RouterLink>
    </div>
  </section>
</template>

<style scoped>
.share-pack { display: flex; align-items: center; justify-content: space-between; gap: 25px; margin: 28px 0 0; padding: 20px 22px; border: 1px solid var(--line); background: var(--sand); }
.share-pack > div:first-child { display: flex; flex-direction: column; }
.share-kicker { color: var(--sea); font-size: .58rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.share-pack strong { margin-top: 3px; font-size: .9rem; }
.share-pack small { margin-top: 3px; color: var(--muted); font-size: .64rem; }
.share-actions { display: flex; flex: 0 0 auto; gap: 8px; }
.share-actions button, .share-actions a { display: inline-flex; align-items: center; gap: 7px; padding: 9px 12px; border: 1px solid var(--line); background: var(--paper); color: var(--text); font-size: .68rem; font-weight: 750; text-decoration: none; cursor: pointer; }
.share-actions button:hover, .share-actions a:hover { border-color: var(--coral); color: var(--coral-dark); }
@media (max-width: 750px) { .share-pack { align-items: flex-start; flex-direction: column; } .share-actions { flex-wrap: wrap; } }
</style>
