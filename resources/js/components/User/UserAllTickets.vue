
<template>
  <div>
    <v-btn color="#356427" @click="openModal">Add Ticket</v-btn>
    <v-select
      v-model="statusFilter"
      :items="statusOptions"
      label="Status"
      class="search-field"
      variant="outlined"
      placeholder="Select status"
      density="compact"
    ></v-select>

    <div class="sorting-options" v-if="filteredTickets.length > 0">
      <span>Sort by:</span>
      <v-icon @click="sortBy('title')">{{
        sortField === "title" && sortDirection === "asc"
          ? "mdi-arrow-up"
          : "mdi-arrow-down"
      }}</v-icon>
      <v-icon @click="sortBy('content')">{{
        sortField === "content" && sortDirection === "asc"
          ? "mdi-arrow-down"
          : "mdi-arrow-up"
      }}</v-icon>
    </div>
    <v-dialog v-model="modalOpen" max-width="500">
      <v-card>
        <v-card-title>Add Ticket</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="ticket.title"
            label="Title"
            variant="outlined"
            density="compact"
          ></v-text-field>
          <v-textarea
            v-model="ticket.content"
            label="Content"
            variant="outlined"
            density="compact"
          ></v-textarea>
          <v-select
            variant="outlined"
            v-model="ticket.category"
            :items="Category"
            item-title="name"
            item-value="id"
            label="Select Category"
            density="compact"
          ></v-select>
          <v-file-input
            label="Attachment"
            v-model="ticket.Attachment"
            variant="outlined"
            density="compact"
          ></v-file-input>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="addTicket">Submit</v-btn>
          <v-btn color="error" @click="closeModal">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="isEditTicketModalOpen" max-width="600px">
      <v-card>
        <v-card-title>Edit Ticket</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="editedTicket.title"
            label="Title"
            variant="outlined"
            density="compact"
          ></v-text-field>
          <v-textarea
            v-model="editedTicket.content"
            label="Content"
            variant="outlined"
            density="compact"
          ></v-textarea>
          <v-select
            variant="outlined"
            v-model="editedTicket.category"
            :items="Category"
            item-title="name"
            item-value="id"
            label="Select Category"
            density="compact"
          ></v-select>
          <v-file-input
            label="Attachment"
            v-model="editedTicket.Attachment"
            variant="outlined"
            density="compact"
          ></v-file-input>
        </v-card-text>
        <v-card-actions>
          <v-btn @click="saveEditedTicket">Save</v-btn>
          <v-btn @click="closeEditTicket">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-row v-if="filteredTickets.length > 0">
      <v-col
        v-for="ticket in paginatedTickets"
        :key="ticket.id"
        md="12"
        lg="12"
      >
        <v-card>
          <v-card-actions style="position: absolute; top: 0; right: 0">
            <v-icon @click="editTicket(ticket.id)" style="color: green">
              mdi-pencil
            </v-icon>
            <v-icon @click="deleteTicket(ticket.id)" style="color: red">
              mdi-delete-outline
            </v-icon>
          </v-card-actions>

          <div class="ticket-header">
            <v-avatar>
              <img :src="defaultUserImage" class="ticket-attachment" />
            </v-avatar>
            <v-card-title @click="viewTicket(ticket.id)" class="ticket-title">
              {{ ticket.title }}
            </v-card-title>
          </div>
          <v-card-text>
            <p>
              {{ truncateDescription(ticket.content) }}
              <span
                v-if="isDescriptionLong(ticket.content)"
                class="read-more"
                @click="viewTicket(ticket.id)"
              >
                Read More
              </span>
            </p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <div v-else class="noticket">No tickets found.</div>
    <div style="display: flex; justify-content: flex-end">
      <v-pagination
        v-if="filteredTickets.length > 0"
        v-model="currentPage"
        :length="totalPages"
        @input="paginate"
        rounded="circle"
        color="secondary"
      ></v-pagination>
      <span v-if="filteredTickets.length > 0">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
    </div>
  </div>
</template>

  <script>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
export default {
  name: "UserTicket",
  setup() {
    const modalOpen = ref(false);
    const searchQuery = ref("");
    const sortField = ref("");
    const sortDirection = ref("asc");
    const ticket = ref({
      title: "",
      content: "",
      Attachment: [],
      category: "",
    });
    const defaultUserImage = ref("/storage/assest/img1.png");
    const statusMap = ref({});
    const statusFilter = ref("All");
    const statusOptions = ref([]);
    const isEditTicketModalOpen = ref(false);
    const editedTicket = ref({
      id: "",
      title: "",
      content: "",
      Attachment: [],
      category: "",
    });

    const Category = ref([]);
    const fetchCategory = async () => {
      try {
        axios.get("/user/categories").then((response) => {
          Category.value = response.data;
        });
      } catch (error) {
        console.error("Error fetching job types:", error);
      }
    };
    const selectcategory = (selectedCategory) => {
      ticket.category = selectedCategory.name;
    };
    const tickets = ref([]);
    const editTicket = (ticketId) => {
      const ticketToEdit = tickets.value.find(
        (ticket) => ticket.id === ticketId
      );
      console.log(ticketToEdit);
      if (ticketToEdit) {
        editedTicket.value.id = ticketToEdit.id;
        editedTicket.value.title = ticketToEdit.title;
        editedTicket.value.content = ticketToEdit.content;
        editedTicket.value.Attachment = ticketToEdit.Attachment;
        editedTicket.value.category = ticketToEdit.category_id;
        isEditTicketModalOpen.value = true;
      } else {
        console.error("Ticket not found!");
      }
    };
    const viewTicket = (ticketId) => {
      window.location.href = `/user/view-ticket/${ticketId}`;
    };
    const filteredTickets = computed(() => {
      return tickets.value.filter((ticket) => {
        const matchesSearch =
          ticket.title
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase()) ||
          ticket.content
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());

        const statusName = statusMap.value[ticket.status_id];

        const matchesStatus =
          !statusFilter.value ||
          statusFilter.value === "All" ||
          statusName === statusFilter.value;

        return matchesSearch && matchesStatus;
      });
    });
    const truncateDescription = (description) => {
      if (description && description.length > 490) {
        return description.substring(0, 490) + "...";
      }
      return description;
    };
    const isDescriptionLong = (description) => {
      return description && description.length > 490;
    };
    const deleteTicket = (ticketId) => {
      window.Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          axios
            .delete(`/user/tickets/${ticketId}`)
            .then((response) => {
              if (response.data.status) {
                tickets.value = tickets.value.filter(
                  (ticket) => ticket.id !== ticketId
                );
                window.Swal.fire({
                  title: "Deleted!",
                  text: "Your ticket has been deleted.",
                  icon: "success",
                });
              }
            })
            .catch((error) => {
              console.error("Error deleting ticket:", error);
              window.Swal.fire({
                title: "Error!",
                text: "Failed to delete the ticket.",
                icon: "error",
              });
            });
        }
      });
    };
    const closeEditTicket = () => {
      isEditTicketModalOpen.value = false;
    };
    const saveEditedTicket = async () => {
      try {
        const formData = new FormData();
        formData.append("title", editedTicket.value.title);
        formData.append("content", editedTicket.value.content);
        formData.append("Attachment", editedTicket.value.Attachment);
        formData.append("category_id", editedTicket.value.category);
        const response = await axios.post(
          `/user/tickets/${editedTicket.value.id}`,
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          }
        );

        if (response.status === 200) {
          isEditTicketModalOpen.value = false;
          window.location.reload();
        }
      } catch (error) {
        console.error("Error updating ticket:", error);
      }
    };

    const fetchTickets = () => {
      axios
        .get("/user/fetch-tickets")
        .then((response) => {
          tickets.value = response.data.data;
        })
        .catch((error) => {
          console.error("Error fetching tickets:", error);
        });
    };
    const fetchStatus = async () => {
      try {
        const response = await axios.get("/agent/status");
        const uniqueStatusNames = [
          ...new Set(response.data.map((status) => status.name)),
        ];
        statusOptions.value = ["All", ...uniqueStatusNames];
        response.data.forEach((status) => {
          statusMap.value[status.id] = status.name;
        });
      } catch (error) {
        console.error("Error fetching statuses:", error);
      }
    };
    onMounted(() => {
      fetchTickets();
      fetchCategory();
      fetchStatus();
    });
    const openModal = () => {
      modalOpen.value = true;
    };
    const closeModal = () => {
      modalOpen.value = false;
    };
    const addTicket = () => {
      const formData = new FormData();
      for (let key in ticket.value) {
        if (key !== "Attachment") {
          formData.append(key, ticket.value[key]);
        } else {
          formData.append("Attachment", ticket.value[key][0]);
        }
      }
      axios
        .post("/user/tickets", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          if (response.data.status == true) {
            window.Swal.fire({
              toast: true,
              position: "top-end",
              timer: 2000,
              showConfirmButton: false,
              icon: "success",
              title: "Ticket Created Successfully",
            });
            closeModal();
            window.location.reload();
          }
        })
        .catch((error) => {
          console.error("Error:", error);
        });
    };
    const sortBy = (field) => {
      if (field === sortField.value) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
      } else {
        sortField.value = field;
        sortDirection.value = "asc";
      }
      tickets.value.sort((a, b) => {
        if (sortDirection.value === "asc") {
          return a[field].localeCompare(b[field]);
        } else {
          return b[field].localeCompare(a[field]);
        }
      });
    };
    const currentPage = ref(1);
    const ticketsPerPage = 4;
    const totalPages = computed(() =>
      Math.ceil(filteredTickets.value.length / ticketsPerPage)
    );
    const paginatedTickets = computed(() => {
      const startIndex = (currentPage.value - 1) * ticketsPerPage;
      const endIndex = startIndex + ticketsPerPage;
      return filteredTickets.value.slice(startIndex, endIndex);
    });
    const paginate = (page) => {
      currentPage.value = page;
    };
    return {
      modalOpen,
      openModal,
      closeModal,
      addTicket,
      ticket,
      editTicket,
      deleteTicket,
      tickets,
      isEditTicketModalOpen,
      editedTicket,
      closeEditTicket,
      saveEditedTicket,
      viewTicket,
      searchQuery,
      filteredTickets,
      sortField,
      sortDirection,
      sortBy,
      currentPage,
      totalPages,
      paginatedTickets,
      paginate,
      selectcategory,
      fetchCategory,
      Category,
      statusFilter,
      statusOptions,
      truncateDescription,
      isDescriptionLong,
      defaultUserImage,
    };
  },
};
</script>
  <style scoped>
.ticket-attachment {
  width: 90px;
  height: 50px;
  transition: width 0.3s, height 0.3s;
}
.search-field {
  width: 200px;
  margin-left: auto;
  margin-right: 30px;
}
.noticket {
  text-align: center;
  font-size: 20px;
}
.sorting-options {
  margin-top: 10px;
  margin-bottom: 10px;
}
.sorting-options span {
  margin-right: 10px;
}
.ticket-title {
  color: #356427;
  text-decoration: none;
  transition: color 0.3s ease-in-out;
  cursor: pointer;
}

.ticket-title:hover {
  color: #558b2f;
  text-decoration: underline 1px;
}
.read-more {
  color: #007bff;
  cursor: pointer;
  text-decoration: underline;
  transition: color 0.3s ease-in-out;
}

.read-more:hover {
  color: #0056b3;
}
.ticket-header {
  display: flex;
}
</style>
  