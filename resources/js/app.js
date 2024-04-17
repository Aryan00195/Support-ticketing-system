/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// import "./bootstrap";
import "@mdi/font/css/materialdesignicons.css";
import { createApp } from "vue";
import "vuetify/styles";
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});
const vuetify = createVuetify({
    components,
    directives,
});
import ExampleComponent from "./components/ExampleComponent.vue";
app.component("example-component", ExampleComponent);

import NavbarComponent from "./components/NavbarComponent.vue";
app.component("navbar-component", NavbarComponent);

import UserAllTickets from "./components/User/UserAllTickets.vue";
app.component("userall-ticket", UserAllTickets);

import ViewTicket from "./components/User/ViewTicket.vue";
app.component("view-ticket", ViewTicket);

import AgentNavbarComponent from "./components/Agent/AgentNavbarComponent.vue";
app.component("agentnav-component", AgentNavbarComponent);

import AgentTicket from "./components/Agent/AgentTicket.vue";
app.component("agent-ticket", AgentTicket);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */
app.use(vuetify);
app.use(VueSweetalert2);
window.Swal = app.config.globalProperties.$swal;
app.mount("#app");
