<template>
  <v-navigation-drawer
    location="left"
    class="left_side_bar"
    v-model="drawer"
    :rail="rail"
  >
    <h3 class="title" v-if="!rail">Tickets</h3>

    <v-list>
      <v-list-item
        :class="{ group: true, active: currentRoute === '/user/ticket' }"
        href="/user/ticket"
        prepend-icon="mdi-ticket-account"
        title="Tickets"
      >
      </v-list-item>

      <v-list-item
        class="dropdown"
        prepend-icon="mdi-logout"
        title="Logout"
        value="Dashboard"
        @click="logout"
      ></v-list-item>
    </v-list>
  </v-navigation-drawer>

  <v-app-bar height="46" id="header">
    <v-app-bar-nav-icon
      variant="text"
      @click.stop="rail = !rail"
      style="color: white"
    ></v-app-bar-nav-icon>
    <v-spacer></v-spacer>
    <v-menu class="log_and_reg_drop" transition="slide-y-transition"> </v-menu>
  </v-app-bar>
</template>
  <script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
  name: "Navbar",

  setup() {
    const users = ref([]);
    const currentRoute = ref(window.location.pathname);
    const drawer = ref(true);
    const rail = ref(false);

    const requests = ref([]);

    const updateRoute = () => {
      currentRoute.value = window.location.pathname;
    };

    const hasPermission = (permission) => {
      if (users.value && users.value.permissions) {
        return users.value.permissions.includes(permission);
      }
      return false;
    };
    const hasrole = (role) => {
      if (users.value && users.value.roles) {
        return users.value.roles.includes(role);
      }
      return false;
    };

    onMounted(() => {
      window.addEventListener("popstate", updateRoute);
    });
    const logout = () => {
      axios.get("/logout").then((response) => {
        if (response.data.status) {
          window.location.href = "/";
        }
      });
    };

    return {
      users,
      logout,
      currentRoute,
      drawer,
      rail,
      requests,
      updateRoute,

      hasPermission,
      hasrole,
    };
  },
};
</script>
  <style scoped>
header.dah_header-bar {
  box-shadow: 0 6px 15px rgba(64, 79, 104, 0.05);
  border-bottom: 1px solid #ecedf2;
}

a {
  text-decoration: none;
}

nav.left_side_bar .v-list-group--open {
  margin-bottom: 10px;
}

nav.left_side_bar .v-list-group--open a.v-list-item {
  padding-inline-start: unset !important;
  font-size: 15px;
  padding-left: 13px !important;
}

nav.left_side_bar {
  border-color: transparent;
  box-shadow: -1px 2px 4px -1px;
}

#logoheading {
  font-size: 35px;
  margin-top: 7px;
  color: black;
  font-family: "Lemon", serif;
  letter-spacing: 2px;
}

.cursor {
  color: black;
  text-decoration: none;
  font-family: "Kanit", sans-serif;
  cursor: pointer;
}

#user {
  margin: 6px;
  font-size: 15px;
  font-family: "Kanit", sans-serif;
  color: #0b0c0c;
  text-transform: capitalize;
}

.dropdown {
  cursor: pointer;
  font-family: "Kanit", sans-serif;
  color: #0a0c0c;
}

.v-app-bar {
  border-bottom: 1px solid #161414;
}

.menu {
  color: #030303;
}

#header {
  background-color: #356427;
}

.links {
  font-size: 5px;
  font-family: Georgia, "Times New Roman", Times, serif;
  font-weight: 200;
  color: black;
  text-decoration: none;
}

.group {
  font-size: 20px;
  font-family: Georgia, "Times New Roman", Times, serif;
  font-weight: 200;
  color: black;
  text-decoration: none;
}

.main {
  margin-bottom: 0px;
}

.title {
  color: black;
  margin-top: 15px;
  margin-left: 32%;
  font-family: Georgia, "Times New Roman", Times, serif;
}

.log_and_reg_drop .v-list {
  width: 160px;
}

#div {
  background-color: white;
}

.log_and_reg_drop .v-list .v-list-item:hover {
  color: #fff;
}
</style>
  