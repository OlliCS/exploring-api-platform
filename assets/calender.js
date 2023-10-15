import { createApp } from 'vue';

const app = createApp({
    template: `
        <div>
            <h1>Hello {{ firstName }}</h1>
        </div>
    `,
    data() {
        return {
            firstName: 'Olli'
        }
    }
}).mount('#app');