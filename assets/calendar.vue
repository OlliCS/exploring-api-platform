<template>
  <div class="wrap">
    <div class="left">
      <h2>How many people ?</h2>
      <input class="input" type="number" v-model="people" min="2" max="100"/>
      <h2>Select a date:</h2>
      <DayPilotNavigator id="nav" :config="navigatorConfig" />

    </div>
    <div class="content">
      <DayPilotCalendar id="dp" :config="config" ref="calendar" />
    </div>
  </div>
</template>

<script>
import {DayPilot, DayPilotCalendar, DayPilotNavigator} from '@daypilot/daypilot-lite-vue'

export default {
  name: 'Calendar',
  data: function() {
    return {
      events: [],
      people: 2,
      navigatorConfig: {
        showMonths: 1,
        skipMonths: 1,
        weekStarts: 1,
        selectMode: "Day",
        startDate: new Date().toISOString().split("T")[0],
        onTimeRangeSelected: args => {
          this.config.startDate = args.day;
          this.fetchEvents();
        }
      },
      config: {
        viewType: "Day",
        businessBeginsHour: 8,
        businessEndsHour: 20,
        timeFormat: "Clock24Hours",
        startDate: new Date().toISOString().split("T")[0],
        durationBarVisible: false,
        timeRangeSelectedHandling: "Disabled",
        eventsLoadMethod:"POST",
        weekStarts: 1,
        onTimeRangeSelected: async (args) => {
          const modal = await DayPilot.Modal.prompt("Create a new event:", "Event 1");
          const dp = args.control;
          dp.clearSelection();
          if (modal.canceled) {
            return;
          }
          dp.events.add({
            start: args.start,
            end: args.end,
            id: DayPilot.guid(),
            text: modal.result
          });


        },
        eventMoveHandling: "Disabled",
        eventDeleteHandling: "Disabled",
        onEventMoved: () => {
          console.log("Event moved");
        },
        onEventResized: () => {
          console.log("Event resized");
        },
      },
    }
  },
  props: {
  },
  components: {
    DayPilotCalendar,
    DayPilotNavigator
  },
  computed: {
    // DayPilot.Calendar object - https://api.daypilot.org/daypilot-calendar-class/
    calendar() {
      return this.$refs.calendar.control;
    }
  },
  methods: {
    async fetchEvents(){
      try{
        const response = await fetch('https://127.0.0.1:8000/api/searches',{
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            "people": people,
            "date": this.config.startDate,
          })
        });
        const data = await response.json();
        console.log(this.config.startDate);
        console.log(data);
        return data;
      }catch(err){
        console.log(err);


      }
    },
    loadEvents() {
      

      // placeholder for an HTTP call
      const events = [
        {
          id: 1,
          start: "2023-10-15T10:00:00",
          end: "2023-10-15T11:00:00",
          text: "Event 1",
          backColor: "#6aa84f",
          borderColor: "#38761d",
        },
        {
          id: 2,
          start: "2022-02-28T13:00:00",
          end: "2022-02-28T16:00:00",
          text: "Event 2",
          backColor: "#f1c232",
          borderColor: "#bf9000",
        },
        {
          id: 3,
          start: "2022-03-01T13:30:00",
          end: "2022-03-01T16:30:00",
          text: "Event 3",
          backColor: "#cc4125",
          borderColor: "#990000",
        },
        {
          id: 4,
          start: "2022-03-01T10:30:00",
          end: "2022-03-01T12:30:00",
          text: "Event 4"
        },
      ];
      this.calendar.update({events});
    },
  },
  mounted() {
    this.loadEvents();
    this.fetchEvents();

  }
}
</script>
<style>
.wrap {
  display: flex;
}

.left {
  margin-right: 10px;
}

.content {
  flex-grow: 1;
}

.input {
  margin-top: 10px;
  width: 100%;
  box-sizing: border-box;
  padding: 5px;
  font-size: 16px;
}


.calendar_default_event_inner {
  background: #2e78d6;
  color: white;
  border-radius: 5px;
  opacity: 0.9;
}
</style>