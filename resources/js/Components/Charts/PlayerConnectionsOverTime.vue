<template>
  <div class="relative full-height">
    <apexchart
      ref="chart"
      v-if="series"
      width="100%"
      :height="400"
      :options="chartOptions"
      :series="series"
      @selection="selection"
      @beforeZoom="clearSelection"
      @beforeResetZoom="clearSelection"
    />
    <div v-if="!data.length" class="chart-no-data">No data found</div>
  </div>
</template>

<script>
import dayjs from 'dayjs'

export default {
  props: {
    data: {
      type: Array,
      required: true,
      default: () => [],
    },
  },

  setup() {
    return {
      series: null,
      unixConnections: [],
    }
  },

  data: () => {
    return {
      chartOptions: {
        chart: {
          id: 'player-connections-over-time',
          type: 'line',
          foreColor: '#fff',
          zoom: {
            enabled: true,
          },
          toolbar: {
            show: true,
            tools: {
              download: false,
              zoom: true,
              selection: true,
              reset: true,
            },
            autoSelected: 'selection',
          },
          selection: {
            enabled: true,
            type: 'x',
            fill: {
              color: '#ffd125',
              opacity: 0.2,
            },
          },
          animations: {
            enabled: false,
          },
        },
        fill: {
          opacity: 1,
        },
        dataLabels: {
          enabled: false,
        },
        xaxis: {
          type: 'datetime',
          axisTicks: {
            show: false,
          },
          axisBorder: {
            color: '#333',
          },
          tooltip: {
            enabled: false,
          },
        },
        yaxis: [
          {
            forceNiceScale: true,
            labels: {
              formatter: function (val) {
                return val.toFixed(0)
              },
            },
          },
          // {
          //   opposite: true,
          //   labels: {
          //     show: false,
          //   },
          //   min: 0,
          //   max: (max) => max + 1,
          // },
        ],
        stroke: {
          curve: ['stepline', 'smooth'],
          lineCap: 'round',
          width: [1, 2],
        },
        markers: {
          size: 0,
          strokeWidth: 0,
        },
        grid: {
          show: true,
          borderColor: '#333',
        },
        colors: ['#ffd125', '#1c80b4'],
        tooltip: {
          theme: 'gh',
          x: {
            format: 'dd MMM yyyy',
          },
          y: [
            {
              formatter: function (y) {
                return y
              },
            },
            {
              formatter: function (y) {
                if (typeof y !== 'undefined') {
                  return y.toFixed(2)
                }
                return y
              },
            },
          ],
        },
      },
    }
  },

  methods: {
    buildGraphData() {
      if (!this.data.length) return
      // this.unixConnections = []

      // let currentDate = dayjs(this.data[0].created_at).startOf('day')
      const endDate = dayjs(this.data[this.data.length - 1].x).endOf('day')
      // let connectionIdx = 0
      // let nextYear = currentDate.endOf('year')

      const connectionsByDay = []
      const roundIds = []

      for (const connection of this.data) {
        connectionsByDay.push({ x: connection.x, y: connection.y })
        roundIds.push(connection.round_ids)
      }

      // const trendData = [[((currentDate.startOf('year').unix() + nextYear.unix()) / 2) * 1000, 0]]
      // // Check every day between the start of connection data and today
      // while (currentDate <= endDate) {
      //   const endOfDay = currentDate.endOf('day')

      //   let connections = 0
      //   let connection = this.data[connectionIdx]
      //   let connectionCreated = dayjs(connection.created_at)
      //   // Add up the connections for this day
      //   while (connection && connectionCreated <= endOfDay) {
      //     connections++

      //     connectionIdx++
      //     connection = this.data[connectionIdx]
      //     if (connection) {
      //       connectionCreated = dayjs(connection.created_at)
      //     }
      //   }

      //   if (connections) {
      //     connectionsByDay.push([currentDate.unix() * 1000, connections])
      //   }

      //   // Get average amount of connections per year for trend line
      //   if (currentDate <= nextYear) {
      //     trendData[trendData.length - 1][1] += connections
      //   } else {
      //     nextYear = nextYear.add(1, 'year')
      //     const startOfYear = currentDate.startOf('year').unix()
      //     const endOfYear = nextYear.unix()
      //     trendData.push([((startOfYear + endOfYear) / 2) * 1000, connections])
      //   }

      //   currentDate = currentDate.add(1, 'day')
      // }

      // Make sure the chart always ends today
      const today = dayjs()
      if (endDate.isBefore(today)) {
        connectionsByDay.push({ x: today.unix() * 1000, y: 0 })
      }

      // // Add unix time data to every connection, for use in the selection method
      // this.unixConnections = this.data.map((connection) => {
      //   connection.unix_created_at = dayjs(connection.created_at).unix() * 1000
      //   return connection
      // })

      console.log('connectionsByDay', connectionsByDay)
      console.log('roundIds', roundIds)
      this.series = [
        {
          name: 'Connections',
          data: connectionsByDay,
          type: 'line',
        },
      ]

      // if (this.dataByYear.length > 1) {
      //   this.series.push({
      //     name: 'Yearly Average',
      //     data: this.dataByYear,
      //     type: 'line',
      //   })
      // }

      this.chartOptions.yaxis[0].labels.show = !!this.data.length
    },

    selection(chartContext, { xaxis }) {
      // const selectedConnections = []
      console.log('xaxis', xaxis)
      // for (const connection of this.unixConnections) {
      //   if (connection.unix_created_at >= xaxis.min && connection.unix_created_at <= xaxis.max) {
      //     selectedConnections.push(connection)
      //   }
      // }
      // this.$emit('selected-connections', selectedConnections)
    },

    clearSelection() {
      this.$refs.chart.updateOptions({
        chart: {
          selection: {
            xaxis: {
              min: undefined,
              max: undefined,
            },
          },
        },
      })
      this.$emit('selected-connections', [])
    },
  },

  watch: {
    data: {
      immediate: true,
      handler() {
        this.buildGraphData()
      },
    },
  },
}
</script>
