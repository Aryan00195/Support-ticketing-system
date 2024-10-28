
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
            @update:modelValue="fetchSubcategories"
          ></v-select>
          <v-select
            v-model="selectedSubcategory"
            :items="subcategories"
            label=" Select Subcategory"
            item-title="name"
            item-value="id"
            variant="outlined"
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
            @update:modelValue="fetcheditSubcategories"
          ></v-select>
          <v-select
            v-model="editedTicket.subcategory"
            :items="subcategories"
            label=" Select Subcategory"
            item-title="name"
            item-value="id"
            variant="outlined"
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
          <div class="ticket-info">
            <!-- <span class="ticket-status">{{ ticket.status_name }}</span> -->
            <span
              :style="{ backgroundColor: getStatusColor(ticket.status_name) }"
              class="ticket-status"
              >{{ ticket.status_name }}</span
            >
            <span class="last-comment-date">{{ lastCommentDate(ticket) }}</span>
          </div>
          <v-card-actions style="position: absolute; top: 10%; right: 0">
            <v-icon @click="editTicket(ticket.id)" style="color: green">
              mdi-pencil
            </v-icon>
            <v-icon @click="deleteTicket(ticket.id)" style="color: red">
              mdi-delete-outline
            </v-icon>
          </v-card-actions>
          <div class="ticket-header">
            <!-- <v-avatar>
              <img :src="defaultUserImage" class="ticket-attachment" />
            </v-avatar> -->
            <v-card-title @click="viewTicket(ticket.id)" class="ticket-title">
              {{ ticket.title }}
            </v-card-title>
            <!-- <span class="last-comment-date">last activity:  {{ lastCommentDate(ticket) }}</span>
            <span class="ticket-status">{{ ticket.status_name }}</span> -->
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
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
export default {
  name: "UserTicket",
  setup() {
    const modalOpen = ref(false);
    const searchQuery = ref("");
    const sortField = ref("");
    const sortDirection = ref("asc");
    const subcategories = ref([]);
    const selectedCategory = ref(null);
    const selectedSubcategory = ref(null);
    const ticket = ref({
      title: "",
      content: "",
      Attachment: [],
      category: "",
    });
    const statusMap = ref({});
    const statusFilter = ref("All");
    const statusOptions = ref([]);
    const status = ref([]);
    const isEditTicketModalOpen = ref(false);
    const editedTicket = ref({
      id: "",
      title: "",
      content: "",
      Attachment: [],
      category: "",
      subcategory: "",
    });
    const getStatusColor = computed(() => {
      return (statusName) => {
        if (statusName === "Open") {
          return "#FFA500";
        } else if (statusName === "Closed") {
          return "#d50000";
        } else if (statusName === "In Progress") {
          return "#FFFF00";
        } else {
          return "#64dd17";
        }
      };
    });
    const Category = ref([]);
    const fetchCategory = async () => {
      try {
        axios.get("/user/categories").then((response) => {
          Category.value = response.data.data;
          console.log(Category.value);
        });
      } catch (error) {
        console.error("Error fetching job types:", error);
      }
    };
    const fetchSubcategories = async () => {
      // selectedCategory.value="";
      try {
        const response = await axios.get(
          `/user/subcategories?category_id=${ticket.value.category}`
        );
        subcategories.value = response.data.data;
      } catch (error) {
        console.error("Error fetching subcategories:", error);
      }
    };
    const fetcheditSubcategories = async () => {
      // selectedCategory.value="";
      try {
        const response = await axios.get(
          `/user/subcategories?category_id=${editedTicket.value.category}`
        );
        subcategories.value = response.data.data;
      } catch (error) {
        console.error("Error fetching subcategories:", error);
      }
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
        editedTicket.value.subcategory = ticketToEdit.subcategory_id;
        selectedSubcategory.value = ticketToEdit.subcategory_id;
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
        formData.append("subcategory_id", editedTicket.value.subcategory);
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
          window.Swal.fire({
            toast: true,
            position: "top-end",
            timer: 2000,
            showConfirmButton: false,
            icon: "success",
            title: "Ticket updated Successfully",
          });
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
          status.value = response.data.data[0].status;
          // console.log(response.data.data[0].status);
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
      //  fetchSubcategories();
    });
    const openModal = () => {
      modalOpen.value = true;
    };
    const closeModal = () => {
      modalOpen.value = false;
    };
    // const addTicket = () => {
    //   const formData = new FormData();
    //   for (let key in ticket.value) {
    //     if (key !== "Attachment") {
    //       formData.append(key, ticket.value[key]);
    //     } else {
    //       formData.append("Attachment", ticket.value[key][0]);
    //     }
    //   }
    //   axios
    //     .post("/user/tickets", formData, {
    //       headers: {
    //         "Content-Type": "multipart/form-data",
    //       },
    //     })
    //     .then((response) => {
    //       if (response.data.status == true) {
    //         window.Swal.fire({
    //           toast: true,
    //           position: "top-end",
    //           timer: 2000,
    //           showConfirmButton: false,
    //           icon: "success",
    //           title: "Ticket Created Successfully",
    //         });
    //         closeModal();
    //         window.location.reload();
    //       }
    //     })
    //     .catch((error) => {
    //       console.error("Error:", error);
    //     });
    // };
    const addTicket = () => {
      const formData = new FormData();
      for (let key in ticket.value) {
        if (key !== "Attachment") {
          formData.append(key, ticket.value[key]);
        } else {
          formData.append("Attachment", ticket.value[key][0]);
        }
      }

      formData.append("subcategory_id", selectedSubcategory.value);

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
    const lastCommentDate = (ticket) => {
      if (ticket.comments && ticket.comments.length > 0) {
        const lastComment = ticket.comments[ticket.comments.length - 1];
        const commentDate = new Date(lastComment.created_at);
        const currentDate = new Date();
        const timeDifference = currentDate - commentDate;
        const daysDifference = Math.floor(
          timeDifference / (1000 * 60 * 60 * 24)
        );
        if (daysDifference === 0) {
          return "today";
        } else if (daysDifference === 1) {
          return "1 day ago";
        } else {
          return `${daysDifference} days ago`;
        }
      }
      return "No comments";
    };
    watch(selectedCategory, (newValue) => {
      if (newValue !== null) {
        fetchSubcategories(newValue);
      }
    });
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
      // selectcategory,
      fetchCategory,
      Category,
      statusFilter,
      statusOptions,
      truncateDescription,
      isDescriptionLong,
      lastCommentDate,
      status,
      getStatusColor,
      selectedCategory,
      selectedSubcategory,
      subcategories,
      fetchSubcategories,
      fetcheditSubcategories,
      // defaultUserImage,
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
  align-items: center;
}

.ticket-title {
  color: #356427;
  text-decoration: none;
  transition: color 0.3s ease-in-out;
  cursor: pointer;
}

.last-comment-date {
  color: #222721;
  font-size: 12px;
}

.ticket-status.open,
.ticket-status.closed {
  color: green;

  border-radius: 3px;
  margin-left: 10px;
  font-size: 12px;
}

.ticket-status.open {
  background-color: #356427;
}

.ticket-status.closed {
  background-color: #d50000;
}
.ticket-info {
  display: flex;
  align-items: center;
  margin-right: 10px;
  float: right;
}
.ticket-status {
  color: black;
  padding: 3px 8px;
  border-radius: 3px;
  margin-right: 10px;
  font-size: 12px;
}
</style>