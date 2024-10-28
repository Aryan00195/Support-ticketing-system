/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// import "./bootstrap";
import "@mdi/font/css/materialdesignicons.css";
import { createApp } from "vue";
import "vuetify/styles";
import axios from "axios";
import { createVuetify } from "vuetify";
import "devextreme/dist/css/dx.common.css";
import "devextreme/dist/css/dx.light.css";
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

import NavbarComponent from "./components/User/NavbarComponent.vue";
app.component("navbar-component", NavbarComponent);

import AdminNavbarComponent from "./components/Admin/NavbarComponent.vue";
app.component("admin-navbar-component", AdminNavbarComponent);

import AdminUserComponent from "./components/Admin/UserComponent.vue";
app.component("admin-user-component", AdminUserComponent);

import AdminTicketComponent from "./components/Admin/TicketsComponent.vue";
app.component("admin-ticket-component", AdminTicketComponent);

import AdminProfileComponent from "./components/Admin/ProfileComponent.vue";
app.component("admin-profile-component", AdminProfileComponent);

import AdminAgentComponent from "./components/Admin/AgentComponent.vue";
app.component("admin-agent-component", AdminAgentComponent);

import AdminDashboardComponent from "./components/Admin/DashboardComponent.vue";
app.component("admin-dashboard-component", AdminDashboardComponent);

import AdminCategoryComponent from "./components/Admin/CategoryComponent.vue";
app.component("admin-category-component", AdminCategoryComponent);


import AdminSubCategoryComponent from "./components/Admin/SubCategoryComponent.vue";
app.component("admin-subcategory-component", AdminSubCategoryComponent);

// import UserAllTickets from "./components/User/UserAllTickets.vue";
// app.component("userall-ticket", UserAllTickets);

import UserAllTickets from "./components/User/UserAllTickets.vue";
app.component("userall-ticket", UserAllTickets);

import ViewTicket from "./components/User/ViewTicket.vue";
app.component("view-ticket", ViewTicket);

import AgentNavbarComponent from "./components/Agent/AgentNavbarComponent.vue";
app.component("agent-nav-component", AgentNavbarComponent);

import AgentTicket from "./components/Agent/AgentTicket.vue";
app.component("agent-ticket", AgentTicket);

import AgentViewTicket from "./components/Agent/AgentViewTicket.vue";
app.component("agent-view-ticket", AgentViewTicket);


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
import {
    DxDataGrid,
    DxPager,
    DxPaging,
    DxFilterRow,
    DxColumn,
    DxButton as DxGridButton,
    DxSearchPanel,
    DxSummary,
    DxTotalItem,
    DxFormat,
    DxHeaderFilter,
    DxScrolling,
    DxMasterDetail,
    DxSorting,
    DxLoadPanel,
    DxItem as DxGridItem,
    DxToolbar,
    DxSelection,
    DxColumnChooser,
    DxEditing,
    DxLookup,
    DxPatternRule,
    DxRequiredRule,
    DxEmailRule,
    DxExport,
} from "devextreme-vue/data-grid";
import {
    DxForm,
    DxItem as DxFormItem,
    DxLabel,
    DxGroupItem,
} from "devextreme-vue/form";
import DxPieChart from 'devextreme-vue/pie-chart';
import { DxTooltip } from "devextreme-vue/tooltip";
import { DxTabPanel, DxItem as DxTabItem } from "devextreme-vue/tab-panel";
import { DxBox, DxItem as DxBoxItem } from "devextreme-vue/box";
import { DxButton } from "devextreme-vue/button";
import { DxDropDownBox } from "devextreme-vue/drop-down-box";
import DxChart, {
    DxLegend,
    DxSeries,
    DxCommonSeriesSettings,
} from "devextreme-vue/chart";
import DxList from "devextreme-vue/list";
const component = {
    DxDataGrid,
    DxPager,
    DxPaging,
    DxFilterRow,
    DxColumn,
    DxGridButton,
    DxButton,
    DxSearchPanel,
    DxSummary,
    DxTotalItem,
    DxFormat,
    DxHeaderFilter,
    DxScrolling,
    DxToolbar,
    DxGridItem,
    DxSorting,
    DxLoadPanel,
    DxMasterDetail,
    DxSelection,
    DxColumnChooser,
    DxForm,
    DxLabel,
    DxGroupItem,
    DxTooltip,
    DxEditing,
    DxFormItem,
    DxLookup,
    DxPatternRule,
    DxRequiredRule,
    DxEmailRule,
    DxExport,
    DxTabPanel,
    DxBox,
    DxBoxItem,
    DxTabItem,
    DxList,
    DxDropDownBox,
    DxChart,
    DxSeries,
    DxLegend,
    DxCommonSeriesSettings,DxPieChart,
};
Object.entries(component).forEach(([name, component]) => {
    app.component(name, component);
});
app.use(vuetify);

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

app.use(VueSweetalert2);
window.Swal = app.config.globalProperties.$swal;

app.mount("#app");
