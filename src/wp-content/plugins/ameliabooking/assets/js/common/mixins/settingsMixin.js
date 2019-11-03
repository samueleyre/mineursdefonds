export default {

  data: () => ({}),

  methods: {
    getSettingsSchedule () {
      let weekSchedule = this.$root.settings.weekSchedule
      let weekDayList = []

      // set week schedule from settings
      weekSchedule.forEach(function (weekDay, index) {
        let timeOutList = []

        // set breaks
        weekDay.breaks.forEach(function (breakItem) {
          timeOutList.push({
            id: null,
            startTime: breakItem.time[0] + ':00',
            endTime: breakItem.time[1] + ':00'
          })
        })

        // set periods
        let periodList = []

        if (weekDay.time[0] !== null && weekDay.time[1] !== null) {
          // check if periods exist in settings
          if (!('periods' in weekDay)) {
            periodList.push({
              id: null,
              startTime: weekDay.time[0] + ':00',
              endTime: weekDay.time[1] + ':00',
              serviceIds: [],
              periodServiceList: [],
              savedPeriodServiceList: []
            })
          } else {
            weekDay.periods.forEach(function (periodItem) {
              periodList.push({
                id: null,
                startTime: periodItem.time[0] + ':00',
                endTime: periodItem.time[1] + ':00',
                serviceIds: [],
                periodServiceList: [],
                savedPeriodServiceList: []
              })
            })
          }
        }

        if (weekDay.time[0] && weekDay.time[1]) {
          weekDayList.push(
            {
              dayIndex: index + 1,
              id: null,
              startTime: weekDay.time[0] + ':00',
              endTime: weekDay.time[1] + ':00',
              periodList: periodList,
              timeOutList: timeOutList
            }
          )
        }
      })

      return weekDayList
    }
  }

}
