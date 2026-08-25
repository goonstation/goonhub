import dayjs from 'dayjs'
import useHelpers from './helpers'

export default function useFormats() {
  const helpers = useHelpers()

  return {
    number: (val) => {
      return new Intl.NumberFormat().format(val)
    },
    percent: (val, digits = 2) => {
      if (!val) return '0%'
      return val.toLocaleString('en-US', { style: 'percent', minimumFractionDigits: digits })
    },
    date: (val, noTime) => {
      if (!val) return
      const date = dayjs.utc(val)
      if (!date.isValid()) return
      let format = 'ddd, D MMM YYYY'
      if (!noTime) {
        format += ', HH:mm'
      }
      return date.local().format(format)
    },
    dateWithTime: (val) => {
      if (!val) return
      const date = dayjs.utc(val)
      if (!date.isValid()) return
      return date.local().format('ddd, D MMM YYYY, HH:mm')
    },
    fromNow: (val) => {
      const date = dayjs.utc(val)
      if (!date.isValid()) return 'never'
      return date.fromNow()
    },
    capitalize: (val) => {
      if (!val) return val
      return val.charAt(0).toUpperCase() + val.slice(1)
    },
    currency: (val) => {
      return val + ' ⪽'
    },
    server: (val, short = false) => {
      return helpers.serverIdToFriendlyName(val, short)
    },
  }
}
