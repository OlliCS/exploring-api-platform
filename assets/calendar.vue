<template>
  <div class="container">
  <div class="wrap">
    <div class="left">
      <h2>Meeting scheduler</h2>
      <input class="input" type="number" v-model="people" min="2" max="100" />
        <DayPilotNavigator id="nav" :config="navigatorConfig" />
        <p class="errorMessage">{{errorMessage}}</p>
    </div>
    <div class="content">
        <DayPilotCalendar id="dp" :config="config" ref="calendar" />
    </div>
  </div>
</div>
</template>

<script>
import { DayPilot, DayPilotCalendar, DayPilotNavigator } from '@daypilot/daypilot-lite-vue'
import {Modal} from "@daypilot/modal";
import moment from 'moment'

export default {
  name: 'Calendar',
  data: function () {
    return {
      freeTimeSlots: [],
      people: 2,
      errorMessage: "",
      navigatorConfig: {
        showMonths: 1,
        skipMonths: 1,
        weekStarts: 1,
        selectMode: "Day",
        startDate: new Date().toISOString().split("T")[0],
        onTimeRangeSelected: args => {
          this.handleDateChangeInNavigator(args);
        }
      },
      config: {
        viewType: "Day",
        timeFormat: "Clock24Hours",
        weekStarts: 1, // Monday
        businessBeginsHour: 8,
        businessEndsHour: 20,
        dayBeginsHour: 8,
        dayEndsHour: 20,
        durationBarVisible: false,
        heightSpec: "BusinessHours",
        height: 5000,
        timeRangeSelectedHandling: "Enabled",
        eventMoveHandling: "Enabled",
        eventDeleteHandling: "Disabled",
        eventResizeHandling: "Disabled",
        startDate: new Date().toISOString().split("T")[0],
        eventClickHandling: "JavaScript",
        eventsLoadMethod: "POST",
        onTimeRangeSelected: async (args) => {
          this.handleTimeSelectedNotTimeSlot(args);
        },
        onEventMoved: async (args) => {
          this.handleTimeSlotMoving(args);
        },
        onEventClicked: (args) => {
          this.eventBooking(args.e);
        },
      },
    }
  },
  watch: {
    people(newVal, oldVal) {
      if (newVal !== oldVal) {
        console.log("Number input changed to:", newVal);
        this.fetchTimeSlots();
      }
    }
  },
  props: {
  },
  components: {
    DayPilotCalendar,
    DayPilotNavigator,
    Modal
  },
  computed: {
    calendar() {
      return this.$refs.calendar.control;
    }
  },
  methods: {
   async handleTimeSelectedNotTimeSlot(args){
      const modal = await DayPilot.Modal.alert("Please select a timeslot");
          const dp = args.control;
          dp.clearSelection();
          if (modal.canceled) {
            return;
          }
    },
    async handleTimeSlotMoving(args){
      const modal = await DayPilot.Modal.alert("You can't move a timeslot");
          const dp = args.control;
          dp.clearSelection();
          if (modal.canceled) {
            return;
          }
    },
    handleDateChangeInNavigator(args) {
    var today = new DayPilot.Date().getDatePart();
          this.config.startDate = args.day;
          if (args.start < today) {
            args.preventDefault();
            this.errorMessage = "You can't select a day in the past";
            return;
          }

          if (args.day.dayOfWeek() === 6 || args.day.dayOfWeek() === 0) {
            args.preventDefault();
            this.errorMessage = "You can't select a weekend day";
            return;
          }

          this.errorMessage = "";
          this.fetchTimeSlots();
    },
    async fetchTimeSlots() {
      try {
        if (this.config.startDate == null) {
          this.config.startDate = new Date().toISOString().split("T")[0];
        }
        const response = await fetch('https://127.0.0.1:8000/api/searches', {
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
        this.convertJsonInTimeSlots(data);
        this.loadTimeSlots();
        return data;
      } catch (err) {
        console.log(err);
      }
    },
    loadTimeSlots() {
      const freeTimeSlots = this.freeTimeSlots;
      try {
        this.calendar.events.list = freeTimeSlots;
      }
      catch (err) {
        console.log(err);
      }
      this.calendar.update({ freeTimeSlots });
    },
    async eventBooking(e) {
      const form = [
        { 
          name: "Room", 
          id: "text", 
          type: "text",
          disabled: true,
        },
        {
          name:"Start time",
          id: "start",
          type:"datetime",
          disabled: true,
        },
        {
          name:"End time",
          id: "end",
          type:"datetime",
          disabled: false,
          focused: false,
        },
        {
          name:"Email",
          id: "email",
          type:"text",
          disabled: false,
          focused: true
        }
      ];

      const formData = e.data;
      const modal = await DayPilot.Modal.form(form, formData,{focus:e.data.end});
      if (modal.canceled) {
        return;
      }
      //call api to save event
      const response = await fetch('https://127.0.0.1:8000/api/bookings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          "startDate": event.data.start,
          "endDate": event.data.end,
          "room": "/api/rooms/" + event.data.id,
        })
      });

      const data = await response.json();

      if (response.ok) {
        this.fetchTimeSlots();
        this.loadTimeSlots();
        this.errorMessage = "";
        this.message = "Booking saved";
        this.detailMessage = `${event.data.text}`
      }
      else{
        this.errorMessage = "Failed saving booking";
        this.message = "";
        this.detailMessage = `${response.status}`;
      }
      console.log(data);
    },

    convertJsonInTimeSlots(data) {
      const colors = [
        // Greens
        "#CCFFCC", // Very light green
        "#99FF99", // Light green
        "#66FF66", // Slightly dark light green

        // Blues
        "#CCFFFF", // Very light blue
        "#99FFFF", // Light blue
        "#66FFFF", // Slightly dark light blue
      ];
      this.freeTimeSlots = [];
      // Iterate over each room and its index
      data.forEach((roomData, roomIndex) => {
        // Safe-checks for data structure
        if (!roomData.room || !roomData.slots) {
          console.error("Invalid data structure:", roomData);
          return;  // Skip this iteration if the data structure is not as expected
        }

        const room = roomData.room;
        const slots = roomData.slots;

        const color = colors[roomIndex % colors.length];

        // Iterating through each time slot
        for (let timeSlot of slots) {
          // Constructing the event object
          let e = {
            id: room.id,
            start: moment(timeSlot.startDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            end: moment(timeSlot.endDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            text: room.name + " (" + room.capacity + ")",
            backColor: color,
          }
          this.freeTimeSlots.push(e);
        }
      });

      this.loadTimeSlots();

    },

    mounted() {
      this.fetchTimeSlots();
    }
  }
}
</script>







<style>
.wrap {
  display: flex;
  margin-left: 100px;

}

.left {
  margin-right: 100px;
  margin-top: 100px;
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
  margin-bottom: 10px;
}

.calendar_default_event_inner {
  background: #2e78d6;
  color: white;
  border-radius: 5px;
  opacity: 0.9;
}

.container{
  height: 200vh;
  overflow: hidden;
  position: fixed;
  top: 0;

  
}

.errorMessage{
  color: red;
}





</style>