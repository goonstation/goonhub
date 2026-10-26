import useFormats from '@/Composables/formats'
import { computed } from 'vue'

const UNITS = new Map([
  ['s', 1],
  ['ms', 1e-3],
  ['us', 1e-6],
  ['%', 0.01],
])
const COMPARISONS = new Map([
  ['>', (a, b) => a > b],
  ['>=', (a, b) => a >= b],
  ['<', (a, b) => a < b],
  ['<=', (a, b) => a <= b],
  ['=', (a, b) => a === b],
])

// Seconds as "s.mmmuuu" colored parts
export function splitTime(seconds) {
  const micros = Math.round(Math.abs(seconds) * 1e6)
  if (!micros) return [{ text: '0', class: 'text-grey-7' }]
  const s = Math.floor(micros / 1e6)
  const ms = Math.floor(micros / 1e3) % 1000
  const us = micros % 1000
  const parts = []
  if (s) parts.push({ text: `${s}.`, class: 'text-negative' })
  if (s || ms)
    parts.push({ text: s ? String(ms).padStart(3, '0') : String(ms), class: 'text-primary' })
  parts.push({ text: s || ms ? String(us).padStart(3, '0') : String(us), class: '' })
  return parts
}

const timeText = (seconds) =>
  splitTime(seconds)
    .map((part) => part.text)
    .join('')

// Red for more, green for less (sorry r-g colorblindness)
export const deltaClass = (value) =>
  value > 0 ? 'text-negative'
  : value < 0 ? 'text-positive'
  : 'text-grey-7'

// Type a proc is defined on, e.g. /datum/gas_mixture/proc/share -> /datum/gas_mixture
const procType = (row) =>
  row.path.replace(/\/(proc|verb)\/$/, '/').replace(/\/$/, '') || 'global procs'

// Procs overridden in several places (e.g. /atom/movable/Move) are listed once per override.
// So, we merge them by default.
function mergeOverrides(entries, sendmaps, merge) {
  const byName = new Map()
  const seen = new Map()
  for (const entry of entries) {
    const nth = (seen.get(entry.name) ?? 0) + 1
    seen.set(entry.name, nth)
    const id = merge || nth === 1 ? entry.name : `${entry.name}#${nth}`
    const existing = byName.get(id)
    if (!existing) {
      const split = entry.name.lastIndexOf('/') + 1
      byName.set(id, {
        ...entry,
        id,
        path: entry.name.slice(0, split),
        proc: entry.name.slice(split),
        count: 1,
      })
    } else if (sendmaps) {
      existing.count++
      existing.value += entry.value
      existing.calls += entry.calls
    } else {
      existing.count++
      existing.self += entry.self
      existing.over += entry.over
      existing.total = Math.max(existing.total, entry.total)
      existing.real = Math.max(existing.real, entry.real)
      existing.calls = Math.max(existing.calls, entry.calls)
    }
  }
  return byName
}

const perCall = (value, calls) => (calls ? value / calls : 0)

// Profiles are generally not a fixed time, these are the mostly-comparable values.
const COMPARABLE = ['pct', 'self_call', 'total_call', 'value_call']

// The columns of a profile, in display order. "delta" columns only exist when comparing.
function columnSpecs(sendmaps, timedValue, comparing) {
  const valueKind = timedValue ? 'time' : 'number'
  const specs =
    sendmaps ?
      [
        { name: 'value', label: timedValue ? 'value (s)' : 'value', kind: valueKind },
        {
          name: 'pct',
          label: 'value %',
          kind: 'percent',
          value: (row, totals) => perCall(row.value, totals.value),
        },
        {
          name: 'value_call',
          label: 'value/call',
          kind: valueKind,
          value: (row) => perCall(row.value, row.calls),
        },
        { name: 'calls', label: 'calls', kind: 'number' },
      ]
    : [
        { name: 'self', label: 'self', kind: 'time' },
        {
          name: 'pct',
          label: 'self %',
          kind: 'percent',
          value: (row, totals) => perCall(row.self, totals.self),
        },
        {
          name: 'self_call',
          label: 'self/call',
          kind: 'time',
          value: (row) => perCall(row.self, row.calls),
        },
        { name: 'total', label: 'total', kind: 'time' },
        {
          name: 'total_call',
          label: 'total/call',
          kind: 'time',
          value: (row) => perCall(row.total, row.calls),
        },
        { name: 'real', label: 'real', kind: 'time' },
        // Wall time not spent running: sleep(), spawn and waiting on other procs' sleeps
        {
          name: 'sleep',
          label: 'sleep',
          kind: 'time',
          value: (row) => Math.max(0, row.real - row.total),
        },
        { name: 'over', label: 'over', kind: 'time' },
        { name: 'calls', label: 'calls', kind: 'number' },
      ]
  if (!comparing) return specs
  return specs.flatMap((spec) =>
    COMPARABLE.includes(spec.name) ?
      [
        spec,
        {
          name: `${spec.name}_delta`,
          label: `Δ ${spec.label}`,
          kind: 'delta',
          unit: spec.kind,
          of: spec.name,
        },
      ]
    : [spec]
  )
}

// Adds the computed columns to each row
function withDerived(rows, specs, metric) {
  const totals = { [metric]: rows.reduce((sum, row) => sum + row[metric], 0) }
  return rows.map((row) => {
    const full = { ...row }
    for (const spec of specs) if (spec.value) full[spec.name] = spec.value(row, totals)
    return full
  })
}

function termPredicate(token, columns) {
  if (token.startsWith('"')) {
    const phrase = token.replace(/^"|"$/g, '').toLowerCase()
    return (row) => row.name.toLowerCase().includes(phrase)
  }

  if (token.length > 3 && token.toLowerCase().startsWith('re:')) {
    let regex
    try {
      regex = new RegExp(token.slice(3), 'i')
    } catch {
      throw new Error(`Invalid regex ${token.slice(3)}`)
    }
    return (row) => regex.test(row.name)
  }

  const comparison = token.match(/^(\w+)(>=|<=|>|<|=)(-?[\d.]+(?:e-?\d+)?)(s|ms|us|%)?$/i)
  if (comparison) {
    const [, name, operator, amount, unit] = comparison
    const column = columns.find((col) => col.name === name.toLowerCase())
    if (!column) {
      throw new Error(`Unknown column "${name}", use ${columns.map((c) => c.name).join(', ')}`)
    }
    const kind = column.unit ?? column.kind
    if (unit === '%' ? kind !== 'percent' : unit && kind !== 'time') {
      throw new Error(`"${column.name}" can't use ${unit}`)
    }
    const target = Number(amount) * (unit ? UNITS.get(unit.toLowerCase()) : 1)
    const compare = COMPARISONS.get(operator)
    return (row) => compare(row[column.name], target)
  }

  const options = token.toLowerCase().split('|').filter(Boolean)
  return (row) => options.some((option) => row.name.toLowerCase().includes(option))
}

// Every term of the query must match, and "-term" negates it
function parseQuery(query, columns) {
  const tokens = (query || '').match(/-?"[^"]*"?|\S+/g) || []
  try {
    const predicates = tokens.map((token) => {
      const negate = token.length > 1 && token.startsWith('-')
      const predicate = termPredicate(negate ? token.slice(1) : token, columns)
      return negate ? (row) => !predicate(row) : predicate
    })
    return { error: '', test: (row) => predicates.every((predicate) => predicate(row)) }
  } catch (e) {
    return { error: e.message, test: () => true }
  }
}

/**
 * Table and stats for a world.Profile() JSON dump, optionally compared against another dump.
 *
 * @param {import('vue').Ref<Array>} entries
 * @param {import('vue').Ref<Array>} other Empty when not comparing
 * @param {import('vue').Ref<string>} search
 * @param {import('vue').Ref<boolean>} merge Whether overrides of the same proc share a row
 */
export default function useProfiler(entries, other, search, merge) {
  const formats = useFormats()

  // world.Profile(..., "sendmaps", "json") has name/value/calls (and a unit) instead of proc timings
  const isSendmaps = computed(() => 'value' in (entries.value[0] ?? {}))
  const comparing = computed(() => other.value.length > 0)
  const metric = computed(() => (isSendmaps.value ? 'value' : 'self'))
  const specs = computed(() =>
    columnSpecs(isSendmaps.value, entries.value[0]?.unit === 's', comparing.value)
  )

  const current = computed(() => mergeOverrides(entries.value, isSendmaps.value, merge.value))
  const base = computed(() => mergeOverrides(other.value, isSendmaps.value, merge.value))

  const allRows = computed(() => {
    const raw = [...current.value.values()]
    // Procs that only ran in the baseline still show up, with nothing in the current profile
    for (const [id, row] of base.value) {
      if (!current.value.has(id)) raw.push({ ...row, ...zeroed(row) })
    }
    const baseDerived = new Map(
      withDerived([...base.value.values()], specs.value, metric.value).map((row) => [row.id, row])
    )

    return withDerived(raw, specs.value, metric.value).map((row) => {
      for (const spec of specs.value) {
        if (spec.kind === 'delta') {
          row[spec.name] = row[spec.of] - (baseDerived.get(row.id)?.[spec.of] ?? 0)
        }
      }
      if (comparing.value) {
        row.status =
          !base.value.has(row.id) ? 'new'
          : !current.value.has(row.id) ? 'gone'
          : null
      }
      return row
    })
  })

  const cellText = (spec, value) => {
    const kind = spec.unit ?? spec.kind
    const text =
      kind === 'time' ? timeText(value)
      : kind === 'percent' ? formats.percent(value)
      : formats.number(value)
    return spec.kind === 'delta' && value > 0 ? `+${text}` : text
  }

  // Number columns are as wide as their longest value (or header with sort arrow)
  const columns = computed(() => [
    { name: 'name', label: 'proc', field: 'name', align: 'left', sortable: true, required: true },
    ...specs.value.map((spec) => {
      const longest = allRows.value.reduce(
        (max, row) => Math.max(max, cellText(spec, row[spec.name]).length),
        spec.label.length + 2
      )
      const width = `calc(${longest}ch + 32px)`
      return {
        ...spec,
        field: spec.name,
        align: 'right',
        sortable: true,
        format: (value) => cellText(spec, value),
        classes: 'num-col',
        headerClasses: 'num-col',
        headerStyle: `width: ${width}`,
        width,
      }
    }),
  ])

  const filter = computed(() => parseQuery(search.value, specs.value))
  const rows = computed(() => allRows.value.filter(filter.value.test))

  const stats = computed(() => {
    const key = metric.value
    const shown = allRows.value.filter((row) => row.status !== 'gone')
    const sorted = [...shown].sort((a, b) => b[key] - a[key])
    const types = new Map()
    const byDelta =
      comparing.value ? [...allRows.value].sort((a, b) => b.pct_delta - a.pct_delta) : []
    for (const row of sorted) {
      const type = procType(row)
      const entry = types.get(type) ?? { type, [key]: 0, procs: 0 }
      entry[key] += row[key]
      entry.procs++
      types.set(type, entry)
    }
    return {
      metric: key,
      ...summarize(shown, key),
      baseline: comparing.value ? summarize([...base.value.values()], key) : null,
      topProcs: sorted.slice(0, 15),
      topTypes:
        isSendmaps.value ? [] : [...types.values()].sort((a, b) => b[key] - a[key]).slice(0, 15),
      // Procs whose share of the profile grew or shrank the most against the baseline
      changes:
        comparing.value ?
          {
            grew: byDelta.filter((row) => row.pct_delta > 0).slice(0, 10),
            shrank: byDelta
              .filter((row) => row.pct_delta < 0)
              .reverse()
              .slice(0, 10),
          }
        : null,
    }
  })

  return {
    isSendmaps,
    comparing,
    metric,
    columns,
    rows,
    stats,
    total: computed(() => allRows.value.length),
    filterError: computed(() => filter.value.error),
  }
}

function zeroed(row) {
  return Object.fromEntries(
    Object.entries(row)
      .filter(([, value]) => typeof value === 'number')
      .map(([key]) => [key, 0])
  )
}

// Headline numbers of one profile
function summarize(rows, key) {
  const sorted = rows.map((row) => row[key]).sort((a, b) => b - a)
  const sum = (values) => values.reduce((total, value) => total + value, 0)
  const total = sum(sorted)
  return {
    total,
    calls: rows.reduce((calls, row) => calls + row.calls, 0),
    procs: rows.length,
    topShare: [10, 50].map((count) => ({
      count,
      share: total ? sum(sorted.slice(0, count)) / total : 0,
    })),
  }
}
