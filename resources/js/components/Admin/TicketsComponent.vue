<template>
  <div>
    <v-dialog v-model="isEditing" max-width="600px" width="500px">
      <v-card>
        <v-card-title>Edit Row</v-card-title>
        <v-card-text>
          <v-select
            v-model="currentStatus"
            :items="statusOptions"
            label="Status"
            class="search-field"
            variant="outlined"
            placeholder="Select status"
            density="compact"
          ></v-select>
          <v-select
            v-model="currentuser"
            :items="agentUsers.map((user) => user.name)"
            label="Assign To User"
            item-text="name"
            item-value="id"
            variant="outlined"
            density="compact"
          ></v-select>
          <!-- <v-text-field
            v-model="assignAgentId"
            label="Assign Agent ID"
            density="compact"
            variant="outlined"
          ></v-text-field> -->
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="saveChanges">Save</v-btn>
          <v-btn @click="cancelEdit">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <DxDataGrid
      id="grid"
      :remote-operations="true"
      :show-borders="true"
      :data-source="dataSource"
      :column-auto-width="true"
      :allow-column-resizing="true"
      @init-new-row="initNewRow"
      @row-inserted="rowInserted"
      @editing-start="openModal"
      @edit-canceled="cancelEdit"
    >
      <DxEditing  :use-icons="true" mode="popup" />

      <DxSearchPanel :visible="true" />

      <DxColumn data-field="title" data-type="string"> </DxColumn>

      <DxColumn
        data-field="status.name"
        data-type="string"
        :edit-cell-render="statusEditCell"
      >
      </DxColumn>

      <DxColumn data-field="category.name" data-type="string"> </DxColumn>

      <DxColumn data-field="user.name" data-type="string"> </DxColumn>

      <DxColumn data-field="user.email" data-type="string"> </DxColumn>

      <DxColumn data-field="agent.name" data-type="string"> </DxColumn>
      <DxColumn
        caption="Actions"
        alignment="center"
        cell-template="customButtonTemplate"
      ></DxColumn>
      <template #customButtonTemplate="{ data }">
        <div>
          <v-icon class="custom-icon" @click="editRow(data.data)"
            >mdi-pencil</v-icon
          >
          <!-- <v-btn icon @click="deleteRow(data.data)">
          <v-icon>mdi-delete</v-icon>
        </v-btn> -->
        </div>
      </template>
      <DxPaging :page-size="pageSize" />
      <DxPager
        :visible="true"
        :allowed-page-sizes="[10, 15, 20]"
        :display-mode="'compact'"
        :show-page-size-selector="true"
        :show-navigation-buttons="true"
        :show-info="true"
      />
      <DxSummary>
        <DxTotalItem column="id" summary-type="count" />
      </DxSummary>
    </DxDataGrid>
  </div>
</template>
  
  <script>
import dxGridStore from "../../composition/dxGridStore";
import { ref, watch, onMounted } from "vue";

export default {
  name: "CompaniesComponent",
  setup() {
    const params = ref({});
    const showColumn = ref(false);
    const pageSize = ref(10);
    const loadURL = `/admin/get/tickets`;
    const { dataSource } = dxGridStore(loadURL, params, null, null, null);
    const statusOptions = ref([]);
    const agentUsers = ref([]);
    const statusMap = ref({});
    const currentStatus = ref(null);
    const currentuser = ref(null);
    const selectedTicketId = ref(null);
    const fetchStatus = async () => {
      try {
        const response = await axios.get("/agent/status");
        const uniqueStatusNames = [
          ...new Set(response.data.map((status) => status.name)),
        ];
        statusOptions.value = [...uniqueStatusNames];
        response.data.forEach((status) => {
          statusMap.value[status.id] = status.name;
        });
      } catch (error) {
        console.error("Error fetching statuses:", error);
      }
    };
    const cancelEdit = () => {
      isEditing.value = false;
    };
    const fetchAgentUsers = async () => {
      try {
        const response = await axios.get("/admin/get/agent-users");
        agentUsers.value = response.data;
        console.log(agentUsers);
      } catch (error) {
        console.error("Error fetching agent users:", error);
      }
    };

    const openModal = () => {
     
      isEditing.value = true;
    };

    onMounted(() => {
      fetchStatus();
      fetchAgentUsers();
    });

    const statusEditCell = () => {
      return {
        component: "DxLookup",
        dataSource: statusOptions.value,
        valueExpr: "name",
        displayExpr: "name",
      };
    };

    const initNewRow = () => {
      showColumn.value = true;
    };

    const rowInserted = () => {
      showColumn.value = false;
    };

    const isEditing = ref(false);

    const startEdit = () => {
      isEditing.value = true;
    };

    watch(isEditing, (newValue) => {
      console.log("isEditing value changed:", newValue);
    });

    const editRow = (rowData) => {
  console.log("Edit row:", rowData);

  if (rowData && rowData.status && rowData.status.name) {
    currentStatus.value = rowData.status.name;
  } else {
    currentStatus.value = null;
  }
  if (rowData && rowData.agent && rowData.agent.name) {
    currentuser.value = rowData.agent.name;
  } else {
    currentuser.value = null;
  }
  if (rowData && rowData.id) {
    selectedTicketId.value = rowData.id;
  } else {
    selectedTicketId.value = null;
  }
  openModal();
};
    const deleteRow = (rowData) => {
      console.log("Delete row:", rowData);
    };

    const saveChanges = async () => {
      try {
        console.log("Current user:", currentuser.value);
        console.log("Current status:", currentStatus.value);
        const updatedData = {
          assign_agent_id: currentuser.value,
          status_id: currentStatus.value,
        };
        console.log(updatedData);

        if (!selectedTicketId.value) {
          console.error("No ticket selected");
          return;
        }
        const response = await axios.post(
          `/admin/update/ticket/${selectedTicketId.value}`,
          updatedData
        );
        if (response.status === 200) {
          window.Swal.fire({
              toast: true,
              position: "top-end",
              timer: 2000,
              showConfirmButton: false,
              icon: "success",
              title: "Ticket updated Successfully",
            });
          isEditing.value = false;
          window.location.reload();
        } else {
          window.Swal.fire({
              toast: true,
              position: "top-end",
              timer: 2000,
              showConfirmButton: false,
              icon: "error",
              title: "Ticket  not updated Successfully",
            });
        }
      } catch (error) {
        console.error("Error updating ticket:", error);
      }
    };
    return {
      dataSource,
      showColumn,
      initNewRow,
      rowInserted,
      pageSize,
      isEditing,
      startEdit,
      statusEditCell,
      statusOptions,
      editRow,
      selectedTicketId,
      deleteRow,
      openModal,
      cancelEdit,
      currentStatus,
      agentUsers,
      currentuser,
      saveChanges,
    };
  },
};
</script>
  
  <style scoped>
.custom-icon {
  background: none;
  font-size: 20px;
}
</style>
  