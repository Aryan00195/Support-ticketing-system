
<template>
  <div class="ticket-details">
    <v-card class="ticket-card">
      <v-card-text>
        <div class="ticket-header">
          <v-avatar size="40">
            <img
              :src="defaultUserImage"
              alt="Default Avatar"
              class="user-avatar"
            />
          </v-avatar>
          <strong>{{ user.name }}</strong>
          <span class="ticket-date"
            >{{ formatCreatedAt(ticket.created_at) }}<br />
            <span class="ticket-status">{{ ticket.status_name }}</span></span
          >
        </div>
        <v-card-title class="card-title">{{ ticket.title }}</v-card-title>
        <div class="detail-item">
          <strong>Content:</strong> {{ ticket.content }}
        </div>
        <div
          class="detail-item"
          v-if="ticket.Attachment && ticket.Attachment.length > 0"
        >
          <strong>Attachment:</strong>
          <div v-if="isPDFAttachment(ticket.Attachment)">
            <iframe
              :src="`/storage/assest/${ticket.Attachment}`"
              class="ticket-attachment"
              width="100%"
              height="500px"
            ></iframe>
            <div class="attachment-download">
              <span>{{ getAttachmentName(ticket.Attachment) }}</span>
              <div class="icon-container">
                <v-icon>mdi-download</v-icon>
              </div>
            </div>
          </div>
          <div v-else style="display: flex">
            <div class="attachment-download">
              <span>{{ getAttachmentName(ticket.Attachment) }}</span>
            </div>
            <div
              class="icon-container"
              @click="downloadAttachment(ticket.Attachment)"
            >
              <v-icon>mdi-download</v-icon>
            </div>
          </div>
        </div>
      </v-card-text>
      <v-card-actions>
        <v-btn @click="showCommentInput" class="reply-btn" rounded="xl"
          >Reply</v-btn
        >
      </v-card-actions>
    </v-card>
    <v-card class="comment-card" v-if="showCommentBox">
      <v-card-title class="card-title">Add Reply</v-card-title>
      <v-card-text>
        <v-textarea
          v-model="newComment"
          placeholder="Leave a comment"
          variant="outlined"
          dense
        ></v-textarea>
        <v-btn @click="submitComment" class="reply-btn" rounded="xl"
          >Submit</v-btn
        >
      </v-card-text>
    </v-card>
    <div class="comment-info-container">
      <div class="comment-count">
        {{ commentCounts }} Replies
        <v-select
          v-if="filteredComments.length"
          v-model="sortOrder"
          :items="sortOptions"
          label="Sort Order"
          class="search-field"
          variant="outlined"
          density="compact"
        ></v-select>
      </div>
    </div>
    <v-card
      id="comment-section"
      v-if="filteredComments && filteredComments.length"
    >
      <v-card-title class="card-title">Replies</v-card-title>
      <v-card-text>
        <ul class="comment-list">
          <li
            v-for="(comment, index) in filteredComments"
            :key="index"
            class="comment-item"
          >
            <div class="comment-header">
              <strong
                >{{ comment.user.name }}[{{
                  comment.user.roles[0].name
                }}]</strong
              >
              <span class="comment-date">{{
                formatCreatedAt(comment.created_at)
              }}</span>
            </div>
            <div class="comment-content">
              {{ comment.comment_text }}
            </div>
          </li>
        </ul>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
export default {
  name: "viewticket",
  props: {
    data: {
      type: Number,
      default: () => 0,
    },
  },
  setup(props) {
    const ticketId = ref(props.data);
    const ticket = ref({
      comments: [],
    });
    const user = ref({});
    const showCommentBox = ref(false);
    const defaultUserImage = ref("/storage/assest/img1.png");
    const newComment = ref("");
    const searchTerm = ref("");
    const sortOrder = ref("Oldest to Newest");
    const sortOptions = ref(["Oldest to Newest", "Newest to Oldest"]);
    const fetchTicketDetails = async () => {
      try {
        const response = await axios.get(`/user/fetchticket/${ticketId.value}`);
        ticket.value = response.data.data;
        user.value = response.data.data.user;
      } catch (err) {
        console.error(err);
      }
    };
    const commentCounts = computed(() => {
      let count = 0;
      if (ticket.value.comments) {
        ticket.value.comments.forEach((comment) => {
          const roleName = comment.user.roles[0].name;

          if (roleName === "Agent" || roleName === "Admin") {
            count++;
          }
        });
      }
      return count;
    });
    const submitComment = async () => {
      try {
        const response = await axios.post("/user/store-comment", {
          comment: newComment.value,
          ticket_id: ticketId.value,
        });
        if (response.data.status === true) {
          window.Swal.fire({
            toast: true,
            position: "top-end",
            timer: 2000,
            showConfirmButton: false,
            icon: "success",
            title: "Comment Submitted Successfully",
          });
          window.location.reload();
        } else {
          window.Swal.fire({
            toast: true,
            position: "top-end",
            timer: 2000,
            showConfirmButton: false,
            icon: "error",
            title: "Error in comment",
          });
        }
      } catch (err) {
        console.error(err);
      }
    };
    const showCommentInput = () => {
      showCommentBox.value = true;
      const commentSection = document.getElementById("comment-section");
      if (commentSection) {
        commentSection.scrollIntoView({ behavior: "smooth" });
      }
    };
    const formatCreatedAt = (createdAt) => {
      const options = {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      };
      return new Date(createdAt).toLocaleDateString(undefined, options);
    };
    onMounted(() => {
      fetchTicketDetails();
    });
    const filteredComments = computed(() => {
      let filtered = ticket.value.comments || [];
      console.log(filtered);
      if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase();
        filtered = filtered.filter(
          (comment) =>
            comment.comment_text.toLowerCase().includes(term) ||
            comment.user?.name?.toLowerCase().includes(term) ||
            comment.user?.roles[0]?.name?.toLowerCase().includes(term) ||
            new Date(comment.created_at)
              .toLocaleDateString("en-US")
              .toLowerCase()
              .includes(term)
        );
      }
      if (sortOrder.value === "Oldest to Newest") {
        filtered.sort(
          (a, b) => new Date(a.created_at) - new Date(b.created_at)
        );
      } else if (sortOrder.value === "Newest to Oldest") {
        filtered.sort(
          (a, b) => new Date(b.created_at) - new Date(a.created_at)
        );
      }
      return filtered;
    });
    const isPDFAttachment = (fileName) => {
      return fileName.toLowerCase().endsWith(".pdf");
    };
    const getAttachmentName = (filePath) => {
      return filePath.split("/").pop();
    };
    const downloadAttachment = (fileName) => {
      const link = document.createElement("a");
      link.href = `/storage/assest/${fileName}`;
      link.download = fileName.split("/").pop();
      link.click();
    };
    return {
      ticket,
      user,
      newComment,
      showCommentBox,
      submitComment,
      formatCreatedAt,
      showCommentInput,
      commentCounts,
      filteredComments,
      sortOptions,
      searchTerm,
      sortOrder,
      isPDFAttachment,
      downloadAttachment,
      getAttachmentName,
      defaultUserImage,
    };
  },
};
</script>
<style scoped>
.ticket-details {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 30px;
}
.ticket-card,
#comment-section {
  width: 100%;
  margin-bottom: 20px;
}
.card-title {
  background-color: #f5f5f5;
  padding: 10px;
  font-weight: bold;
}
.comment-list {
  list-style-type: none;
  padding: 0;
}
.comment-item {
  margin-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 15px;
}
.comment-content {
  margin-left: 40px;
}
.comment-input {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.reply-btn {
  margin-left: 82%;
  border: 2px solid black;
  color: black;
  padding: 6px 12px;
  display: block;
  min-width: 120px;
}
.reply-btn:hover {
  border-color: #356427;
  background-color: #356427;
  color: white;
}
.ticket-attachment {
  width: 220px;
}
.comment-info-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  margin-top: 20px;
}
.comment-count {
  font-weight: bold;
  color: #747474;
  font-size: 20px;
  display: flex;
}
.search-field {
  margin-left: 150px;
  width: 220px;
}
.ticket-status {
  color: #333;
}
.comment-card {
  width: 100%;
}
.detail-item {
  margin-top: 20px;
}
.attachment-download {
  display: flex;
  justify-content: space-between;
  align-items: center;

  margin-top: 10px;
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  transition: background-color 0.3s ease;
  width: 180px;
}
.attachment-download:hover {
  background-color: #f5f5f5;
}
.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e0e0e0;
  border-radius: 4px;
  width: 36px;
  height: 36px;
  margin-top: 10px;
  cursor: pointer;
}
.attachment-download v-icon {
  font-size: 20px;
  color: #333;
}
.ticket-header {
  display: flex;
  align-items: center;
}
.ticket-date,
.comment-date {
  color: #777;
  font-size: 12px;
  margin-left: auto;
}
.user-avatar {
  width: 90px;
  height: 40px;
}
.comment-header {
  display: flex;
  align-items: center;
  gap: 10px;
}
.comment-date {
  color: #777;
  font-size: 12px;
  margin-left: auto;
}

</style>