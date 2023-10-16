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
import axios from 'axios'

export default {
  name: 'Calendar',
  data: function () {
    return {
      timeSlots: [],
      people: 2,
      colors : [
        // Greens
        "#CCFFCC", // Very light green
        "#99FF99", // Light green
        "#66FF66", // Slightly dark light green

        // Blues
        "#CCFFFF", // Very light blue
        "#99FFFF", // Light blue
        "#66FFFF", // Slightly dark light blue
      ],
      apiBaseUrl: "https://127.0.0.1:8000/api/",
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
          this.createErrorModal("Please select a timeslot",args);
        },
        onEventMoved: async (args) => {
          this.createErrorModal("You can't move a timeslot",args);
        },
        onEventClicked: (args) => {
          this.selectTimeSlotForBooking(args.e);
        },
      },
    }
  },
  watch: {
    async people(newVal, oldVal) {
      if (newVal !== oldVal) {
        console.log("Number input changed to:", newVal);
        await this.loadTimeSlots();
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
    async createErrorModal(message,args){
      const modal = await DayPilot.Modal.alert(message);
      const dp = args.control;
      dp.clearSelection();
      if (modal.canceled) {
        return;
      }
    },
    handleDateChangeInNavigator(args) {
          this.config.startDate = args.day;
          if(!this.checkIfSelectedDateInNavigatorIsValid(args.day)){
            args.preventDefault();
            return;
          }
          this.errorMessage = "";
          this.loadTimeSlots();
    },
    checkIfSelectedDateInNavigatorIsValid(selectedDate){
      var today = new DayPilot.Date().getDatePart();
      if (selectedDate < today) {
        this.errorMessage = "You can't select a day in the past";
        return false;
      }
      if (selectedDate.dayOfWeek() === 6 || selectedDate.dayOfWeek() === 0) {
        this.errorMessage = "You can't select a weekend day";
        return false;
      }
      return true;
    },
    async loadTimeSlots() {
      this.timeSlots = [];
      await this.fetchTimeSlots();
      this.refreshCalendarWithTimeSlots();
    },
    async fetchTimeSlots() {
      try {
        let result = await this.callApi('searches', 'POST', {
            "people": this.people,
            "date": this.config.startDate,
          });
        this.convertApiResponseInTimeSlots(result);
      }catch (err) {
        console.log(err);
      }
    },
    async callApi(endpoint, method, body) {
      let apiUrl = this.apiBaseUrl + endpoint;
      try{
        let response = await axios({
          method: method,
          url: apiUrl,
          data: body,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        return response.data;
        
      }
      catch (err) {
        console.log(err);
      }
    },
    refreshCalendarWithTimeSlots() {
      const freeTimeSlots = this.timeSlots;
      this.timeSlots = [];
      this.calendar.events.list = freeTimeSlots;
      this.calendar.update({freeTimeSlots});
    },
    
    async selectTimeSlotForBooking(e) {
      const bookingform = [
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
        },
        {
          name:"Email",
          id: "email",
          type:"text",
          required: false,
        }
      ];
      const bookingFormData = e.data;
      const modal = await DayPilot.Modal.form(bookingform, bookingFormData,{focus: "email"});
      if (modal.canceled || !modal.result) {
        return;
      }

      try{
        let response = await this.callApi('bookings', 'POST', {
          "startDate": modal.result.start,
          "endDate": modal.result.end,
          "room":  modal.result.id,
        });
        if(response.success === true){
          this.createErrorModal(`Booking successful`,e);
          this.loadTimeSlots(); 
        } 
        else{
          this.createErrorModal(`${response.message}`,e);
        }
      }catch (err) {
        this.createErrorModal(`Booking failed`,e);
        console.log(err);
      }
    },

    convertApiResponseInTimeSlots(roomDataList) {
      roomDataList.forEach((singleRoomData, index) => {
        if (!singleRoomData.room || !singleRoomData.slots) {
          console.error("Invalid data structure:", singleRoomData);
          return;  
        }
        const roomDetails = singleRoomData.room;
        const availableSlots = singleRoomData.slots;
        const roomColor = this.colors[index % this.colors.length];

        for (let slot of availableSlots) {
          let timeSlot = {
            id: roomDetails.id,
            start: moment(slot.startDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            end: moment(slot.endDate, 'YYYY-MM-DDTHH:mm:ss').format('YYYY-MM-DDTHH:mm:ss'),
            text: `${roomDetails.name} ( ${roomDetails.capacity} )`,
            backColor: roomColor,
          }
          this.timeSlots.push(timeSlot);
        }
      });
    },
    mounted() {
      this.loadTimeSlots();
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