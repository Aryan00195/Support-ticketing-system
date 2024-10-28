<template>
  <v-container class="company_dashboard_view">
    <div id="cardsDiv">
      <v-row>
        <v-col sm="12" md="6" lg="3" xl="3" cols="12">
          <v-card class="mx-auto card one_dt" id="Card">
            <v-card-item>
              <div class="flex-container">
                <v-icon class="bottomicon3" size="48"
                  >mdi-ticket-account</v-icon>
              </div>
              <div class="cardtop">
                <h6 class="dashboardh6">Tickets</h6>
                <h1 class="numbering">{{ totalUser }}</h1>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
        <v-col sm="12" md="6" lg="3" xl="3" cols="12">
          <v-card class="mx-auto card two_dt" id="Card">
            <v-card-item>
              <div class="flex-container">
                <v-icon class="bottomicon3" size="48">mdi-account-group</v-icon>
              </div>
              <div class="cardtop">
                <h6 class="dashboardh6">Total Users</h6>
                <h1 class="numbering">{{ totalJobs }}</h1>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
        <v-col sm="12" md="6" lg="3" xl="3" cols="12">
          <v-card class="mx-auto card three_dt" id="Card">
            <v-card-item>
              <div class="flex-container">
                <v-icon class="bottomicon3" size="48">mdi-account</v-icon>
              </div>
              <div class="cardtop">
                <h6 class="dashboardh6">Total Agents</h6>
                <h1 class="numbering">{{ totalposted }}</h1>
              </div>
            </v-card-item>
          </v-card>
        </v-col>
      </v-row>
    </div>
    <div id="chartDiv" class="chart-container">
      <div class="graph">
        <DxChart
          id="chart"
          :data-source="grossProductData"
          title="ticket Added"
          @pointClick="onPointClick"
        >
          <DxExport :enabled="true" />
          <DxCommonSeriesSettings
            argument-field="Month"
            type="bar"
            hover-mode="allArgumentPoints"
            selection-mode="allArgumentPoints"
            barWidth="40"
          >
            <DxLabel :visible="true">
              <DxFormat :precision="0" type="fixedPoint" />
            </DxLabel>
          </DxCommonSeriesSettings>
          <DxSeries
            value-field="ticketCount"
            name="Total Ticket"
            color="#315231"
          />
        </DxChart>
      </div>
      <div class="pie-chart">
        <DxPieChart
          :data-source="agentDistributionData"
          title="Agent Distribution"
        >
          <DxExport :enabled="true" />
          <DxSeries argument-field="label" value-field="value" />
        </DxPieChart>
      </div>
      <div id="SupplierDiv">
        <v-card>
          <v-card-title style="background-color: #315231; color: white"
            >Recent Tickets</v-card-title
          >
          <v-card-text>
            <v-table>
              <thead>
                <tr>
                  <th class="text-left">Title</th>
                  <th class="text-left">status</th>
                  <th class="text-left">Author Name</th>
                  <th class="text-left">Category</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in recentTickets" :key="ticket.id">
                  <td>{{ ticket.title }}</td>
                  <td>{{ ticket.status.name }}</td>
                  <td>{{ ticket.user.name }}</td>
                  <td>{{ ticket.category.name }}</td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </div>
    </div>
  </v-container>
</template>
  <script>
import { ref, onMounted, computed } from "vue";
export default {
  name: "Dashboard",
  setup() {
    const recentTickets = ref([]);
    const totalposted = ref(null);
    const totalJobs = ref(null);
    const totalUser = ref("0");
    const Users = ref([]);
    const grossProductData = ref([]);
    const fetchTotalticket = async () => {
      try {
        const response = await axios.get("/admin/get/totaltickets");
        totalUser.value = response.data.totalCount;
      } catch (err) {
        console.log(err);
      }
    };
    const fetchTotalusers = async () => {
      try {
        const response = await axios.get("/admin/get/totalusers");
        totalJobs.value = response.data.total_users;
      } catch (err) {
        console.log(err);
      }
    };
    const agentDistributionData = computed(() => {
      if (totalposted.value) {
        return [
          { label: "Available Agents", value: totalposted.value * 0.6 },
          { label: "Busy Agents", value: totalposted.value * 0.3 },
          { label: "Offline Agents", value: totalposted.value * 0.1 },
        ];
      }
      return [];
    });
    const fetchTotalagents = async () => {
      try {
        const response = await axios.get("/admin/get/totalagents");
        totalposted.value = response.data.total_agents;
      } catch (err) {
        console.log(err);
      }
    };
    const fetchRecentTickets = async () => {
      try {
        const response = await axios.get("/admin/get/recenttickets");
        recentTickets.value = response.data.recentTickets;
      } catch (err) {
        console.log(err);
      }
    };
    const fetchGraphData = async () => {
      try {
        const response = await axios.get("/admin/graph/data");
        if (response.data.status) {
          grossProductData.value = response.data.data;
        } else {
          console.error("Failed to fetch graph data:", response.data);
        }
      } catch (err) {
        console.error("Error fetching graph data:", err);
      }
    };
    function onPointClick({ target }) {
      target.select();
    }
    onMounted(() => {
      fetchTotalticket();
      fetchTotalusers();
      fetchTotalagents();
      fetchRecentTickets();
      fetchGraphData();
    });
    return {
      recentTickets,
      totalJobs,
      totalUser,
      totalposted,
      Users,
      grossProductData,
      onPointClick,
      agentDistributionData,
    };
  },
};
</script>
  <style >
.graph {
  margin-top: 50px;
  width: 50%;
  height: 400px;
}
.company_dashboard_view #SupplierDiv table tbody {
  display: table-row-group;
}
.company_dashboard_view #SupplierDiv {
  margin: 50px 0px 0px 0px;
  width: 100%;
}
.company_dashboard_view div.card:hover {
  background: #315231;
  color: #fff;
}
.flex-container {
  display: flex;
  align-items: center;
}
.company_dashboard_view div.card:hover .flex-container i.mdi,
.company_dashboard_view div.card:hover h1.numbering,
.company_dashboard_view div.card:hover .cardtop h6.dashboardh6 {
  color: #fff !important;
}
.company_dashboard_view div.card .flex-container i.mdi {
  color: #315231;
  margin-right: 10px;
  background-color: #a7c5a94d;
  border-radius: 25px;
  font-size: 27px !important;
}
.company_dashboard_view div.card:hover .flex-container i.mdi {
  background-color: #0e0f0f4d;
}
.company_dashboard_view .cardtop h6.dashboardh6 {
  font-size: 15px;
}
.company_dashboard_view div.card h1.numbering,
.company_dashboard_view .cardtop h6.dashboardh6 {
  color: #696969;
}
.company_dashboard_view div#chartDiv div#SupplierDiv .v-card-title {
  padding: 3px 15px;
}
.company_dashboard_view div#cardsDiv div#Card .v-card-item__content {
  display: flex;
}
.company_dashboard_view .v-table--density-default {
  --v-table-header-height: 40px;
  --v-table-row-height: 40px;
}
.company_dashboard_view #SupplierDiv table thead th.text-left {
  font-weight: 600;
}
.chart-container {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
}

.graph,
.pie-chart {
  margin-top: 20px;
  margin-right: 20px;
}
</style>