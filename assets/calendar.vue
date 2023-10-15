<template>
  <div>
    <button @click="goToPreviousWeek">Previous Week</button>
    <button @click="goToNextWeek">Next Week</button>
    <DayPilotCalendar id="dp" :config="config" ref="calendar" />
  </div>
</template>

<script>
import { DayPilot, DayPilotCalendar } from '@daypilot/daypilot-lite-vue'

export default {
  name: 'Calendar',
  data: function () {
    return {
      config: {
        viewType: "Week",
      },
    }
  },
  props: {
  },
  components: {
    DayPilotCalendar
  },
  computed: {
    calendar() {
      return this.$refs.calendar.control;
    }
  },
  methods: {
    loadEvents() {
      // placeholder for an AJAX call
      const events = [
        {
          id: 1,
          start: "2023-10-17T10:00:00",
          end: "2023-10-17T11:00:00",
          text: "Event 1",
        },
        {
          id: 2,
          start: "2022-02-28T13:00:00",
          end: "2022-02-28T16:00:00",
          text: "Event 2",
        },

      ];
      this.calendar.update({ events });
    },


    goToPreviousWeek() {
      const currentDate = this.calendar.getDate(); // Get the current date
      const previousWeekDate = DayPilot.Date.addDays(currentDate, -7); // Subtract 7 days to go back one week
      this.calendar.goToDate(previousWeekDate);
    },
    // Go to the next week
    goToNextWeek() {
      const currentDate = this.calendar.getDate(); // Get the current date
      const nextWeekDate = DayPilot.Date.addDays(currentDate, 7); // Add 7 days to go forward one week
      this.calendar.goToDate(nextWeekDate);
    },

  },
  mounted() {
    this.loadEvents();

  }
}
</script>