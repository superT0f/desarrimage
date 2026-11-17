<script setup>
import { computed } from 'vue'
import CountryFlag from './CountryFlag.vue'
import SharePack from './SharePack.vue'

const props = defineProps({
  guide: { type: Object, required: true },
})

const money = (cents) => new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: props.guide.scenario.currency,
  maximumFractionDigits: cents % 100 === 0 ? 0 : 2,
}).format(cents / 100)

const days = (value) => `${Number(value).toLocaleString('fr-FR')} JH`
const maxYearOne = computed(() => Math.max(...props.guide.costs.map((cost) => cost.year_one_total_cents), 1))
const sortedCosts = computed(() => [...props.guide.costs].sort((a, b) => a.year_one_total_cents - b.year_one_total_cents))
const timelineStyle = (phase) => ({
  left: `${((phase.start_day - 1) / props.guide.scenario.window_days) * 100}%`,
  width: `${(phase.duration_days / props.guide.scenario.window_days) * 100}%`,
})
const riskLabels = { faible: 'Faible', modere: 'Modéré', eleve: 'Élevé' }
const verificationLabels = { draft: 'Brouillon', documented: 'Documenté', tested: 'Testé sur le terrain', verified: 'Vérifié indépendamment' }
const kindLabels = { documentation: 'Documentation', pricing: 'Tarifs', migration: 'Migration', security: 'Sécurité' }
const sharedItemsLabel = computed(() => props.guide.source.slug === 'trello' ? 'tableaux Trello' : 'Drives partagés')
const nativeItemsLabel = computed(() => props.guide.source.slug === 'trello' ? 'cartes avec pièce jointe' : 'de documents natifs')
const commonSources = computed(() => props.guide.sources.filter((source) => !source.target))
const targetSources = (slug) => props.guide.sources.filter((source) => source.target?.slug === slug)
</script>

<template>
  <section id="guide-complet" class="rich-guide">
    <header class="rich-guide-header">
      <div>
        <p class="eyebrow"><span></span> Étude de cas complète</p>
        <h2>{{ guide.title }}</h2>
        <p>{{ guide.summary }}</p>
      </div>
      <div class="guide-update">
        <span class="status-dot"></span>
        <strong>{{ verificationLabels[guide.verification.status] }}</strong>
        <small>Prix vérifiés le {{ guide.verification.price_checked_at }} · scénario chiffré, pas un devis</small>
      </div>
    </header>

    <SharePack :guide="guide" />

    <div class="scenario-grid" role="group" aria-label="Hypothèses du scénario">
      <div><strong>{{ guide.scenario.users }}</strong><span>utilisateurs</span></div>
      <div><strong>{{ guide.scenario.data_gb }} Go</strong><span>de données</span></div>
      <div><strong>{{ guide.scenario.shared_drives }}</strong><span>{{ sharedItemsLabel }}</span></div>
      <div><strong>{{ guide.scenario.native_documents_percentage }} %</strong><span>{{ nativeItemsLabel }}</span></div>
      <div><strong>J{{ guide.scenario.window_days }}</strong><span>fenêtre de bascule</span></div>
    </div>

    <nav class="guide-toc" aria-label="Sommaire du guide">
      <a href="#comparaison">Comparatif</a>
      <a href="#couts">Coûts & JH</a>
      <a href="#planning">Planning</a>
      <a href="#risques">Risques</a>
      <a href="#sources">Sources</a>
    </nav>

    <section id="decisions" class="guide-section decision-section">
      <div class="guide-section-heading">
        <p class="eyebrow"><span></span> La réponse courte</p>
        <h2>Quelle destination pour quel équipage ?</h2>
      </div>
      <div class="decision-grid">
        <article v-for="decision in guide.decisions" :key="decision.target.slug" class="decision-card">
          <span class="product-monogram" :class="decision.target.region" aria-hidden="true">{{ decision.target.name.charAt(0) }}</span>
          <h3>{{ decision.target.name }}</h3>
          <p>{{ decision.recommendation }}</p>
        </article>
      </div>
    </section>

    <section id="comparaison" class="guide-section">
      <div class="guide-section-heading">
        <p class="eyebrow"><span></span> Comparatif fonctionnel</p>
        <h2>Les vraies différences, case par case</h2>
      </div>
      <div class="comparison-table-wrapper">
        <table class="guide-comparison-table">
          <thead>
            <tr>
              <th scope="col">Critère</th>
              <th v-for="target in guide.targets" :key="target.slug" scope="col"><CountryFlag :code="target.country_code" :country-name="target.country_name" />{{ target.name }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in guide.comparison" :key="row.position">
              <th scope="row">{{ row.criterion }}</th>
              <td v-for="target in guide.targets" :key="target.slug">{{ row.values[target.slug] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section id="couts" class="guide-section cost-section">
      <div class="guide-section-heading cost-heading">
        <div>
          <p class="eyebrow light"><span></span> Budget & jours-homme</p>
          <h2>Combien coûte vraiment la traversée ?</h2>
        </div>
        <div class="day-rate"><strong>{{ money(guide.scenario.day_rate_cents) }}</strong><span>par jour-homme</span></div>
      </div>

      <div class="cost-chart" role="img" :aria-label="`Coût total estimé sur un an : ${sortedCosts.map((cost) => `${cost.target.name}, ${money(cost.year_one_total_cents)}`).join(' ; ')}`">
        <div v-for="cost in sortedCosts" :key="cost.target.slug" class="cost-bar-row">
          <span>{{ cost.target.name }}</span>
          <div class="cost-bar-track">
            <div class="cost-bar" :class="cost.target.slug" :style="{ width: `${Math.max(8, (cost.year_one_total_cents / maxYearOne) * 100)}%` }">
              <strong>{{ money(cost.year_one_total_cents) }}</strong>
            </div>
          </div>
        </div>
        <p class="chart-caption">Coût total année 1 : abonnements / hébergement + migration + exploitation interne. {{ guide.scenario.tax_label }}.</p>
      </div>

      <div class="cost-cards">
        <article v-for="cost in guide.costs" :key="cost.target.slug" class="cost-card">
          <div class="cost-card-header">
            <h3>{{ cost.target.name }}</h3>
            <strong>{{ money(cost.year_one_total_cents) }} <small>/ an 1</small></strong>
          </div>
          <dl>
            <div><dt>Récurrent</dt><dd>{{ money(cost.recurring_monthly_cents) }} / mois</dd></div>
            <div><dt>Migration</dt><dd>{{ days(cost.migration_days) }} · {{ money(cost.migration_cost_cents) }}</dd></div>
            <div><dt>Exploitation / an</dt><dd>{{ days(cost.annual_operation_days) }} · {{ money(cost.annual_operation_cost_cents) }}</dd></div>
            <div><dt>Années suivantes</dt><dd>{{ money(cost.year_two_total_cents) }} / an</dd></div>
          </dl>
          <p>{{ cost.notes }}</p>
          <details>
            <summary>Voir la formule et les hypothèses</summary>
            <p><strong>{{ cost.recurring_label }}</strong></p>
            <code>({{ money(cost.recurring_monthly_cents) }} × 12) + ({{ days(cost.migration_days) }} × {{ money(guide.scenario.day_rate_cents) }}) + ({{ days(cost.annual_operation_days) }} × {{ money(guide.scenario.day_rate_cents) }}) = {{ money(cost.year_one_total_cents) }}</code>
          </details>
        </article>
      </div>

      <div class="assumptions-card">
        <h3>Hypothèses de calcul — à ajuster avant de décider</h3>
        <ul><li v-for="assumption in guide.assumptions" :key="assumption">{{ assumption }}</li></ul>
      </div>
    </section>

    <section id="planning" class="guide-section">
      <div class="guide-section-heading">
        <p class="eyebrow"><span></span> Chronologie</p>
        <h2>Une traversée de {{ guide.scenario.window_days }} jours, sans Big Bang</h2>
      </div>
      <div class="timeline-chart">
        <div class="timeline-ruler" aria-hidden="true" :style="{ '--timeline-days': guide.scenario.window_days }">
          <span v-for="day in guide.scenario.window_days" :key="day">J{{ day }}</span>
        </div>
        <div v-for="phase in guide.timeline" :key="phase.position" class="timeline-row">
          <div class="timeline-label"><strong>{{ phase.phase }}</strong><small>{{ phase.description }}</small></div>
          <div class="timeline-track"><div class="timeline-phase" :style="timelineStyle(phase)">J{{ phase.start_day }}–J{{ phase.start_day + phase.duration_days - 1 }}</div></div>
        </div>
      </div>
    </section>

    <section id="risques" class="guide-section">
      <div class="guide-section-heading">
        <p class="eyebrow"><span></span> Registre des risques</p>
        <h2>Ce qui peut tangenter le bateau</h2>
        <p>Score = probabilité × impact. Une estimation éditoriale pour prioriser les parades, pas une prédiction.</p>
      </div>
      <div class="risk-list">
        <article v-for="risk in guide.risks" :key="`${risk.title}-${risk.target?.slug || 'all'}`" class="risk-card">
          <div class="risk-score" :class="risk.level"><strong>{{ risk.score }}/9</strong><span>{{ riskLabels[risk.level] }}</span></div>
          <div class="risk-content">
            <span class="risk-target">{{ risk.target?.name || 'Toutes les destinations' }}</span>
            <h3>{{ risk.title }}</h3>
            <p>{{ risk.mitigation }}</p>
          </div>
          <div class="risk-meta"><span>Probabilité<strong>{{ risk.probability }}</strong></span><span>Impact<strong>{{ risk.impact }}</strong></span></div>
        </article>
      </div>
    </section>

    <section id="sources" class="guide-section sources-section">
      <div class="guide-section-heading">
        <p class="eyebrow"><span></span> Liens utiles & sources</p>
        <h2>Vérifier avant de larguer les amarres</h2>
      </div>
      <div class="source-groups">
        <div class="source-group">
          <h3>{{ guide.source.name }} — départ</h3>
          <a v-for="source in commonSources" :key="source.url" :href="source.url" target="_blank" rel="noopener">
            <span class="source-kind">{{ kindLabels[source.kind] }}</span><strong>{{ source.label }}</strong><i>↗</i>
          </a>
        </div>
        <div v-for="target in guide.targets" :key="target.slug" class="source-group">
          <h3>{{ target.name }} — arrivée</h3>
          <a v-for="source in targetSources(target.slug)" :key="source.url" :href="source.url" target="_blank" rel="noopener">
            <span class="source-kind">{{ kindLabels[source.kind] }}</span><strong>{{ source.label }}</strong><i>↗</i>
          </a>
        </div>
      </div>
      <p class="source-disclaimer">Sources consultées le {{ guide.updated_at }}. Les prix, fonctions et conditions peuvent évoluer : revérifiez les offres et faites un pilote avant la bascule.</p>
    </section>
  </section>
</template>

<style scoped>
.rich-guide { margin: 80px 0 100px; }
.rich-guide-header { display: grid; grid-template-columns: 1fr 260px; gap: 60px; align-items: end; padding-bottom: 35px; border-bottom: 1px solid var(--line); }
.rich-guide-header h2 { margin-bottom: 18px; }
.rich-guide-header > div:first-child > p:last-child { max-width: 750px; margin: 0; color: var(--muted); }
.guide-update { display: flex; flex-direction: column; padding: 18px; border: 1px solid var(--line); background: var(--sand); }
.guide-update .status-dot { margin-bottom: 12px; }
.guide-update strong { font-size: .8rem; }
.guide-update small { margin-top: 5px; color: var(--muted); font-size: .65rem; line-height: 1.6; }
.scenario-grid { display: grid; grid-template-columns: repeat(5, 1fr); margin: 25px 0 0; border: 1px solid var(--line); }
.scenario-grid > div { display: flex; flex-direction: column; align-items: center; padding: 24px 12px; text-align: center; }
.scenario-grid > div + div { border-left: 1px solid var(--line); }
.scenario-grid strong { font-size: 1.65rem; letter-spacing: -.04em; }
.scenario-grid span { color: var(--muted); font-size: .65rem; }
.guide-toc { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 24px; }
.guide-toc a { padding: 7px 13px; border: 1px solid var(--line); border-radius: 20px; color: var(--sea); font-size: .66rem; font-weight: 750; }
.guide-toc a:hover { border-color: var(--sea); background: var(--sand); }
.guide-section { margin-top: 90px; }
.guide-section-heading { margin-bottom: 35px; }
.guide-section-heading h2 { max-width: 800px; margin-bottom: 14px; font-size: clamp(2rem, 3.5vw, 3rem); }
.guide-section-heading > p:last-child:not(.eyebrow) { color: var(--muted); font-size: .85rem; }
.decision-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.decision-card { padding: 28px; border: 1px solid var(--line); background: var(--sand); }
.decision-card h3 { margin: 22px 0 12px; }
.decision-card p { margin: 0; color: var(--muted); font-size: .82rem; line-height: 1.75; }
.comparison-table-wrapper { overflow-x: auto; border: 1px solid var(--line); }
.guide-comparison-table { width: 100%; min-width: 900px; border-collapse: collapse; table-layout: fixed; }
.guide-comparison-table th, .guide-comparison-table td { padding: 19px 20px; border-bottom: 1px solid var(--line); vertical-align: top; text-align: left; font-size: .76rem; line-height: 1.65; }
.guide-comparison-table thead th { background: var(--ink); color: var(--white); font-size: .75rem; }
.guide-comparison-table thead th:first-child { width: 20%; color: #b6cbc5; }
.guide-comparison-table tbody th { background: var(--sand); font-weight: 750; }
.guide-comparison-table tbody td { color: var(--muted); }
.guide-comparison-table tr:last-child th, .guide-comparison-table tr:last-child td { border-bottom: 0; }
.cost-section { padding: 45px; background: var(--ink); color: var(--white); }
.cost-heading { display: flex; align-items: end; justify-content: space-between; gap: 30px; }
.cost-heading h2 { margin-bottom: 0; }
.day-rate { display: flex; flex-direction: column; flex: 0 0 auto; padding-left: 25px; border-left: 1px solid #3d5b5d; }
.day-rate strong { color: var(--coral); font-size: 1.8rem; }
.day-rate span { color: #b8cac6; font-size: .65rem; }
.cost-chart { padding: 28px; background: #153a3d; }
.cost-bar-row { display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 20px; margin: 16px 0; font-size: .75rem; }
.cost-bar-track { height: 38px; background: rgba(255,255,255,.06); }
.cost-bar { display: flex; align-items: center; justify-content: flex-end; min-width: 100px; height: 100%; padding: 0 13px; background: var(--sea-light); transition: width 500ms ease; }
.cost-bar.nextcloud { background: #6ba9a0; }
.cost-bar.kdrive { background: #ed9b72; }
.cost-bar.cozy-drive { background: var(--coral); }
.cost-bar strong { color: var(--ink-deep); font-size: .8rem; white-space: nowrap; }
.chart-caption { margin: 20px 0 0; color: #9eb7b2; font-size: .65rem; }
.cost-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 20px; }
.cost-card { padding: 23px; background: var(--paper); color: var(--ink); }
.cost-card-header { display: flex; align-items: start; justify-content: space-between; gap: 10px; margin-bottom: 18px; }
.cost-card-header h3 { margin: 0; font-size: 1rem; }
.cost-card-header > strong { color: var(--sea); font-size: .85rem; text-align: right; white-space: nowrap; }
.cost-card-header small { display: block; color: var(--muted); font-size: .55rem; font-weight: 500; }
.cost-card dl { margin: 0; }
.cost-card dl > div { display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; border-top: 1px solid var(--line); font-size: .65rem; }
.cost-card dt { color: var(--muted); }
.cost-card dd { margin: 0; text-align: right; }
.cost-card > p { min-height: 80px; margin: 18px 0; color: var(--muted); font-size: .7rem; line-height: 1.65; }
.cost-card details { border-top: 1px solid var(--line); padding-top: 12px; }
.cost-card summary { color: var(--sea); cursor: pointer; font-size: .65rem; font-weight: 750; }
.cost-card details p { margin: 15px 0 5px; font-size: .65rem; }
.cost-card code { display: block; margin-top: 10px; white-space: normal; font-size: .58rem; line-height: 1.7; }
.assumptions-card { margin-top: 20px; padding: 23px; border: 1px solid #426164; }
.assumptions-card h3 { font-size: .85rem; }
.assumptions-card ul { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 30px; margin: 0; padding-left: 18px; color: #b8cac6; font-size: .67rem; }
.timeline-ruler { display: grid; grid-template-columns: repeat(var(--timeline-days), 1fr); margin-left: 240px; color: var(--muted); font-size: .58rem; text-align: center; }
.timeline-row { display: grid; grid-template-columns: 220px 1fr; align-items: center; gap: 20px; margin-top: 17px; }
.timeline-label { display: flex; flex-direction: column; }
.timeline-label strong { font-size: .76rem; }
.timeline-label small { color: var(--muted); font-size: .6rem; line-height: 1.5; }
.timeline-track { position: relative; height: 32px; background: var(--sand); }
.timeline-phase { position: absolute; top: 0; bottom: 0; display: grid; place-items: center; min-width: 38px; background: var(--sea-strong); color: var(--white); font-size: .58rem; font-weight: 750; }
.timeline-row:nth-child(odd) .timeline-phase { background: var(--coral); color: var(--ink); }
.risk-list { display: grid; gap: 12px; }
.risk-card { display: grid; grid-template-columns: 80px 1fr 170px; gap: 24px; align-items: center; padding: 23px; border: 1px solid var(--line); }
.risk-score { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 66px; height: 66px; border-radius: 50%; background: #e1eee8; color: var(--success); }
.risk-score.modere { background: #f8ecd2; color: #8b651e; }
.risk-score.eleve { background: #f7e4dd; color: var(--coral-dark); }
.risk-score strong { font-size: 1rem; }
.risk-score span { font-size: .52rem; text-transform: uppercase; }
.risk-target { color: var(--sea); font-size: .58rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.risk-content h3 { margin: 5px 0 8px; font-size: .95rem; }
.risk-content p { margin: 0; color: var(--muted); font-size: .72rem; line-height: 1.65; }
.risk-meta { display: flex; gap: 20px; }
.risk-meta > span { display: flex; flex-direction: column; color: var(--muted); font-size: .55rem; }
.risk-meta strong { color: var(--ink); font-size: .68rem; text-transform: capitalize; }
.source-groups { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.source-group { padding: 25px; border: 1px solid var(--line); }
.source-group h3 { margin-bottom: 20px; font-size: 1rem; }
.source-group a { display: grid; grid-template-columns: 85px 1fr auto; gap: 12px; align-items: center; padding: 11px 0; border-top: 1px solid var(--line); font-size: .68rem; }
.source-group a:hover { color: var(--coral-dark); }
.source-kind { color: var(--sea); font-size: .54rem; font-weight: 750; text-transform: uppercase; }
.source-group i { color: var(--coral); font-style: normal; }
.source-disclaimer { margin: 25px 0 0; color: var(--muted); font-size: .67rem; }
@media (max-width: 950px) {
  .scenario-grid { grid-template-columns: repeat(3, 1fr); }
  .scenario-grid > div:nth-child(4) { border-left: 0; border-top: 1px solid var(--line); }
  .scenario-grid > div:nth-child(5) { border-top: 1px solid var(--line); }
  .decision-grid, .cost-cards { grid-template-columns: 1fr; }
  .cost-card > p { min-height: 0; }
  .timeline-ruler { display: none; }
  .risk-card { grid-template-columns: 70px 1fr; }
  .risk-meta { grid-column: 2; }
}
@media (max-width: 650px) {
  .rich-guide { margin-top: 60px; }
  .rich-guide-header { grid-template-columns: 1fr; gap: 25px; }
  .scenario-grid { grid-template-columns: repeat(2, 1fr); }
  .scenario-grid > div:nth-child(3), .scenario-grid > div:nth-child(5) { border-left: 0; }
  .scenario-grid > div:nth-child(3) { border-top: 1px solid var(--line); }
  .scenario-grid > div:nth-child(4) { border-left: 1px solid var(--line); }
  .scenario-grid > div:nth-child(5) { grid-column: 1 / -1; }
  .guide-section { margin-top: 65px; }
  .cost-section { padding: 28px 20px; }
  .cost-heading { align-items: start; flex-direction: column; }
  .day-rate { padding: 0; border: 0; }
  .cost-bar-row { grid-template-columns: 70px 1fr; gap: 10px; }
  .cost-bar { padding: 0 8px; }
  .cost-bar strong { font-size: .65rem; }
  .assumptions-card ul { grid-template-columns: 1fr; }
  .timeline-row { grid-template-columns: 1fr; gap: 8px; margin-bottom: 22px; }
  .risk-card { grid-template-columns: 1fr; gap: 15px; }
  .risk-meta { grid-column: auto; }
  .source-groups { grid-template-columns: 1fr; }
  .source-group a { grid-template-columns: 70px 1fr auto; }
}
</style>
