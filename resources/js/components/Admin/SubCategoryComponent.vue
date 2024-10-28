<template>
  <div>
    <DxDataGrid
      id="grid"
      ref="dataGridRef"
      :remote-operations="true"
      :show-borders="true"
      :data-source="dataSource"
      :column-auto-width="true"
      :allow-column-resizing="true"
      @init-new-row="initNewRow"
      @row-inserted="rowInserted"
      @editing-start="logEvent"
      @edit-canceled="cancelEdit"
      @saving="saveEvent"
    >
      <DxEditing :allow-adding="true" :allow-updating="true" :allow-deleting="true" :use-icons="true" mode="popup" />
      <DxSearchPanel :visible="true" />
      <DxColumn data-field="subcategory_name" caption="Name" />
      <DxColumn data-field="category_name" caption="Category Name" edit-cell-template="categorydropdown" />
      <template #categorydropdown>
        <DxDropDownBox
          :accept-custom-value="true"
          label="Select Category"
          data-type="dropdown"
          labelMode="floating"
          v-model:value="selectedCategory"
          v-model:opened="datadropdown"
          @value-change="selectCategory"
        >
          <DxList :data-source="categoryList"  selection-mode="single" @item-click="selectCategory" />
        </DxDropDownBox>
      </template>
      <template #rolesdropdown>
        <DxDropDownBox
          :accept-custom-value="true"
          label="Select Role"
          labelMode="floating"
          v-model:value="selectedRoleType"
          v-model:opened="roletypedropdown"
        >
          <DxList :data-source="roleTypes" selection-mode="single" @item-click="selectRoleType" />
        </DxDropDownBox>
      </template>
      <template #role-template="{ data: cell }">
        <span v-if="cell && cell.data && cell.data.roles">
          <span v-for="(role, index) in cell.data.roles" :key="index">
            {{ role.name }}
          </span>
        </span>
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
import { ref, onMounted } from "vue";
export default {
  name: "CompaniesComponent",
  setup() {
    const roleTypes = ref(["Admin", "Agent", "User"]);
    const selectedCategory = ref("");
    const datadropdown = ref(false);
    const userId = ref();
    const params = ref({});
    const dataGridRef = ref(null);
    const selectedRoleType = ref("");
    const roletypedropdown = ref(false);
    const categoryList = ref([]);
    const selectRoleType = (e) => {
      selectedRoleType.value = e.itemData;
      params.value = { ...params.value, roleType: selectedRoleType.value };
      roletypedropdown.value = false;
    };
    const allCategories = ref([]);
    const phonePattern = ref("^[0-9]{10,13}$");
    const passwordPattern = ref(/^.{7,}$/);
    const showColumn = ref(false);
    const pageSize = ref(10);
    const loadURL = `/admin/subcategories`;
    const updateURL = `/admin/update/subcategories`;
    const insertURL = `/admin/add/subcategories`;
    const deleteUrl = `/admin/subcategories`;
    const { dataSource, refreshTable } = dxGridStore(loadURL, params, insertURL, updateURL, deleteUrl);
    // const saveEvent = (e) => {
    //     console.log(e);
    //   if (e.changes == 0) {
    //     if (params.value.roleType) {
    //       axios.post(`/admin/update/user/${userId.value}`, {
    //         roleType: params.value,
    //       }).then((response) => {
    //         const keys = Object.keys(params.value);
    //         for (let i = 1; i < keys.length; i++) {
    //           delete params.value[keys[i]];
    //         }
    //         refreshTable(dataGridRef);
    //       });
    //     }
    //   }
    // };
    const saveEvent = (e) => {
      console.log(e.changes);
      if (e.changes.length > 0) { 
      
        axios.post(`/admin/update/subcategories`, e.changes)
          .then((response) => {
          
            refreshTable(dataGridRef);
          })
          .catch((error) => {
            console.error('Error updating data:', error);
          });
      }
    };
    const initNewRow = () => {
      showColumn.value = true;
    };
    const rowInserted = () => {
      showColumn.value = false;
    };
    const logEvent = (e) => {
  const categoryName = e.data.category_name;
  selectedCategory.value = categoryName;
};
    const cancelEdit = () => {
      showColumn.value = false;
    };
    const selectCategory = (e) => {
      let value = e.itemData;
      selectedCategory.value = value;
      const foundCategory = allCategories.value.find(item => item.name === selectedCategory.value);
      if (foundCategory) {
        value = foundCategory.id;
      } else {
        null;
      }
      params.value = { ...params.value, category_id: value };
      datadropdown.value = false;
    };
    const fetchCategories = async () => {
  try {
    const response = await axios.get('/user/categories');
    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      categoryList.value = response.data.data.map(category => category.name);
    
    } else {
      console.error('Error fetching categories: Invalid response data structure');
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};
    onMounted(() => {
      fetchCategories();
    });
    return {
      dataSource,
      showColumn,
      updateURL,
      phonePattern,
      passwordPattern,
      initNewRow,
      rowInserted,
      pageSize,
      roleTypes,
      selectRoleType,
      allCategories,
      selectedRoleType,
      roletypedropdown,
      params,
      logEvent,
      cancelEdit,
      saveEvent,
      userId,
      dataGridRef,
      categoryList,
      selectedCategory,
      datadropdown,
      selectCategory,
    };
  },
};
</script>

<style scoped>
.container {
  margin-top: 15px;
  margin-left: 90px;
  width: 90%;
}

.options {
  padding: 10px;
  margin-top: 10px;
  background-color: rgba(191, 191, 191, 0.15);
}

.caption {
  margin-bottom: 10px;
  font-weight: 500;
  font-size: 18px;
}

.option {
  margin-bottom: 10px;
}

.option > span {
  position: relative;
  top: 2px;
  margin-right: 10px;
}

.option > .dx-widget {
  display: inline-block;
  vertical-align: middle;
}

#requests .caption {
  float: left;
  padding-top: 7px;
}

#requests > div {
  padding-bottom: 5px;
}

#requests > div::after {
  content: "";
  display: table;
  clear: both;
}

#requests #clear {
  float: right;
}

#requests ul {
  list-style: none;
  max-height: 100px;
  overflow: auto;
  margin: 0;
}

#requests ul li {
  padding: 7px 0;
  border-bottom: 1px solid #ddd;
}

#requests ul li:last-child {
  border-bottom: none;
}
</style>
