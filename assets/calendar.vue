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
import moment from 'moment'

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
            "people": this.people,
            "date": this.config.startDate,
          })
        });
        const data = await response.json();
        this.convertBookingJsonToEvents(data);

        return data;
      }catch(err){
        console.log(err);


      }
    },
    loadEvents() {
      const events = this.events;
      this.calendar.update({events});
    },
    convertBookingJsonToEvents(data){
      this.events = [];
      console.log(data);
      for(let booking of data){
        let e = {
          id: booking.id,
          start: moment(booking.start).format('YYYY-MM-DDTHH:mm:ss'),
          end: moment(booking.end).format('YYYY-MM-DDTHH:mm:ss'),
          text: booking.room,
          barColor: "#38761d",
          barBackColor: "#93c47d",
        }

        console.log(e);
        this.events.push(e);

      }


    }
  },
  mounted() {

    this.fetchEvents();
    this.loadEvents();
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