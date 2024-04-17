<template>
    <v-row>
        <v-col cols="12">
            <v-card class="card">
                <v-card-title class=" text-center">Personal Information</v-card-title>
                <v-form @submit.prevent="updateProfile(formData.id)">
                    <v-row>
                        <v-col cols="12">
                            <v-card>
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="2">
                                            <v-avatar size="130px" class="avatar">
                                                <label for="fileInput" @click="openFileInput">
                                                    <span class="mdi mdi-pencil" id="icon"></span>
                                                </label>
                                                <input type="file" id="fileInput" ref="fileInput" style="display: none"
                                                    @change="handleImageChange" />
                                                <img v-if="imageUrl" :src="imageUrl" alt="Selected Image" width="150px"
                                                    height="150px" />
                                            </v-avatar>
                                        </v-col>
                                        <v-col cols="3">
                                            <label for="name" class="custom-text-field">Name</label>
                                            <v-text-field v-model="formData.name" density="compact"
                                                :rules="nameRules"></v-text-field>
                                        </v-col>
                                        <v-col cols="3">
                                            <label for="email" class="custom-text-field">Email</label>
                                            <v-text-field density="compact" v-model="formData.email"
                                                :rules="emailRules"></v-text-field>
                                        </v-col>
                                        <v-col cols="3">
                                            <label for="phone" class="custom-text-field">Contact No.</label>
                                            <v-text-field density="compact" v-model="formData.phone"
                                                :rules="phoneRules"></v-text-field>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                                <v-card-actions>
                                    <v-btn class="bg-primary mx-auto" color="white" type="submit">Update</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-card class="card">
                <v-card-title class=" text-center">Password and Security</v-card-title>
                <v-form @submit.prevent="updatePassword()">
                    <v-row>
                        <v-col cols="12">
                            <v-card>
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="4">
                                            <label for="Current Password" class="custom-text-field">Current
                                                Password</label>
                                            <v-text-field density="compact" v-model="formDetail.current"
                                                :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                                                :type="show1 ? 'text' : 'password'" :rules="currentRules"
                                                @click:append="show1 = !show1"></v-text-field>
                                        </v-col>
                                        <v-col cols="4">
                                            <label for="New Password" class="custom-text-field">New Password</label>
                                            <v-text-field density="compact" v-model="formDetail.new" :rules="newRules"
                                                :append-icon="show2 ? 'mdi-eye' : 'mdi-eye-off'"
                                                :type="show2 ? 'text' : 'password'"
                                                @click:append="show2 = !show2"></v-text-field>
                                        </v-col>
                                        <v-col cols="4">
                                            <label for="Confirm Passwprd" class="custom-text-field">Confirm
                                                Password</label>
                                            <v-text-field density="compact" :rules="confirmRules"
                                                :append-icon="show3 ? 'mdi-eye' : 'mdi-eye-off'"
                                                :type="show3 ? 'text' : 'password'"
                                                @click:append="show3 = !show3"></v-text-field>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                                <v-card-actions>
                                    <v-btn class="bg-primary mx-auto" type="submit" color="white">Save</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card>
        </v-col>
    </v-row>
</template>

<script>
import { ref, nextTick, onMounted } from "vue";
export default {
    name: "ProfileComponent",
    setup() {
        const show1 = ref(false);
        const show2 = ref(false);
        const show3 = ref(false);
        const imageUrl = ref();
        const fileInput = ref();
        const formData = ref({
            id: "",
            name: "",
            email: "",
            phone: "",
        });
        const formDetail = ref({
            current: "",
            new: "",
        });
        const nameRules = ref([
            (v) => !!v || "Full Name is required",
            (v) => (v && v.length >= 3) || "Full Name must be at least 3 characters",
        ]);
        const emailRules = ref([
            (v) => !!v || "E-mail is required",
            (v) => /.+@.+\..+/.test(v) || "E-mail must be valid",
        ]);
        const phoneRules = ref([
            (v) => !!v || "Phone number is required",
            (v) =>
                (v && v.length >= 10) || "Phone number must be a valid 10-digit number",
        ]);
        const currentRules = ref([(value) => !!value || "Password is required"]);
        const newRules = ref([
            (value) => !!value || "Password is required",
            (value) =>
                (value && value.length >= 8) ||
                "Password must be at least 8 characters",
        ]);
        const confirmRules = ref([
            (value) => !!value || "Confirm Password is required",
            (value) => value === formDetail.value.new || "Passwords do not match",
        ]);
        const openFileInput = () => {
            fileInput.value.click();
        };
        const handleImageChange = () => {
            const file = fileInput.value.files[0];
            if (file) {
                if (!file.type.startsWith("image/")) {
                    alert("Please select a valid image file.");
                    fileInput.value.value = null;
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    imageUrl.value = e.target.result;
                };
                reader.readAsDataURL(file);
                formData.value.image = file;
            }
        };
        const fetchProfile = () => {
            window.axios.get('/admin/getProfile').then((response) => {
                formData.value = response.data.user;
                imageUrl.value = response.data.user.user_image;
            }).catch((err) => {
                console.log(err);
            });
        };
        onMounted(async () => {
            await nextTick();
            fileInput.value = document.getElementById("fileInput");
            fetchProfile();
        });
        const updateProfile = (id) => {
            const formDataUpload = new FormData();
            formDataUpload.append("name", formData.value.name);
            formDataUpload.append("email", formData.value.email);
            formDataUpload.append("phone", formData.value.phone);
            if (formData.value.image) {
                formDataUpload.append("user_image", formData.value.image);
            }
            console.log(formDataUpload.keys);
            window.axios
                .post(`./user/update-profile/${id}`, formDataUpload, {
                    header: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    console.log(response.data);
                    if (response.data.status === true) {
                        window.Swal.fire({
                            toast: true,
                            position: "top-end",
                            timer: 2000,
                            showConfirmButton: false,
                            icon: "success",
                            title: "User Profile updated successfully!",
                        });
                    }
                })
                .catch((error) => {
                    console.log("here");
                    window.Swal.fire({
                        toast: true,
                        position: "top-end",
                        timer: 2000,
                        showConfirmButton: false,
                        icon: "error",
                        title: "Something Went Wrong!",
                    });
                });
        };
        const updatePassword = () => {
            console.log(formDetail.value);
            axios
                .post(`./user/update-password`, formDetail.value)
                .then((response) => {
                    console.log(response);
                    if (response.data.status === true) {
                        window.Swal.fire({
                            toast: true,
                            position: "top-end",
                            timer: 2000,
                            showConfirmButton: false,
                            icon: "success",
                            title: "Password updated successfully!",
                        });
                    }
                })
                .catch((error) => {
                    console.log("here");
                    window.Swal.fire({
                        toast: true,
                        position: "top-end",
                        timer: 2000,
                        showConfirmButton: false,
                        icon: "error",
                        title: "Incoreect Password!",
                    });
                });
        };
        return {
            show1,
            show2,
            show3,
            formData,
            formDetail,
            updateProfile,
            updatePassword,
            nameRules,
            emailRules,
            phoneRules,
            currentRules,
            newRules,
            confirmRules,
            imageUrl,
            fileInput,
            openFileInput,
            handleImageChange,
        };
    },
};
</script>

<style>
.custom-text-field {
    margin-top: 30px;
    margin-bottom: 10px;
    font-size: 20px;
    /* color: black */
}

.avatar {
    border: 1px solid black;
}

.v-avatar {
    display: flex;
    flex-direction: column-reverse;
    justify-content: flex-start;
}

#icon {
    position: absolute;
    top: 89%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: black;
    cursor: pointer;
}

.v-input--horizontal .v-input__append {
    margin-inline-start: -28px;
}
</style>