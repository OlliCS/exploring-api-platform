<template>
  <div class="wrap">
    <div class="left">
      <h2>How many people ?</h2>
      <input class="input" type="number" v-model="people" min="2" max="100" />
      <h2>Select a date:</h2>
      <DayPilotNavigator id="nav" :config="navigatorConfig" />
    </div>
    <div class="content">
      <DayPilotCalendar id="dp" :config="config" ref="calendar" />
    </div>
  </div>
</template>





<script>
import { DayPilot, DayPilotCalendar, DayPilotNavigator } from '@daypilot/daypilot-lite-vue'
import moment from 'moment'

export default {
  name: 'Calendar',
  data: function () {
    return {
      freeTimeSlots: [],
      people: 2,
      navigatorConfig: {
        showMonths: 1,
        skipMonths: 1,
        weekStarts: 1,
        selectMode: "Day",
        startDate: new Date().toISOString().split("T")[0],
        onTimeRangeSelected: args => {
          var today = new DayPilot.Date().getDatePart();
          this.config.startDate = args.day;
          if (args.start < today) {
            args.preventDefault();
            alert("Please choose a future date")
            return;
          }
          else{
            this.fetchTimeSlots();
          }
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

        timeRangeSelectedHandling: "Disabled",
        eventMoveHandling: "Disabled",
        eventDeleteHandling: "Disabled",
        eventResizeHandling: "Disabled",

        startDate: new Date().toISOString().split("T")[0],

        eventClickHandling: "JavaScript",

        eventsLoadMethod: "POST",

        onTimeRangeSelected: async (args) => {
          const modal = await DayPilot.Modal.prompt("Do you want to save this timeslot for a meeting:");
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

        eventBooking(event) {
          const confirmBooking = confirm(`Do you want to book ${event.text}?`);
          if (confirmBooking) {
            this.saveBooking(event);
          }
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
    DayPilotNavigator
  },
  computed: {
    calendar() {
      return this.$refs.calendar.control;
    }
  },
  methods: {
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
    async eventBooking(event) {
      console.log(event.data.id);

      const modal = await DayPilot.Modal.prompt(`Do you want to save this timeslot for a meeting: ${event.data.text}`);

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
        alert("Booking saved");

      }
      else {
        alert("Booking not saved");
        console.log("Error booking the slot: " + data);
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
          // Ensure date parsing
          let startHour = moment(timeSlot.startDate, 'YYYY-MM-DDTHH:mm:ss').format('HH:mm');
          let endHour = moment(timeSlot.endDate, 'YYYY-MM-DDTHH:mm:ss').format('HH:mm');

          // Constructing the event object
          let e = {
            id: room.id,
            start: moment(timeSlot.startDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            end: moment(timeSlot.endDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            text: room.name + " (" + room.capacity + ")\t " + startHour + " -  " + endHour,
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