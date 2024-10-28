<template>
    <div>
    <DxDataGrid id="grid" ref="dataGridRef" :remote-operations="true" :show-borders="true" :data-source="dataSource"
        :column-auto-width="true" :allow-column-resizing="true" @init-new-row="initNewRow" @row-inserted="rowInserted"
        @editing-start="logEvent" @edit-canceled="cancelEdit" @saving="saveEvent">
        <DxEditing :allow-adding="true" :allow-updating="true" :allow-deleting="true" :use-icons="true" mode="popup" />
        <DxSearchPanel :visible="true" />
        <DxColumn data-field="name" caption="Name">
        </DxColumn>
        <template #rolesdropdown>
            <DxDropDownBox :accept-custom-value="true" label="Select Role" labelMode="floating"
                v-model:value="selectedRoleType" v-model:opened="roletypedropdown">
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
        <DxPager :visible="true" :allowed-page-sizes="[10, 15, 20]" :display-mode="'compact'"
            :show-page-size-selector="true" :show-navigation-buttons="true" :show-info="true" />
        <DxSummary>
            <DxTotalItem column="id" summary-type="count" />
        </DxSummary>
    </DxDataGrid>
</div>
</template>
<script>
import dxGridStore from "../../composition/dxGridStore";
import { ref } from "vue";
export default {
    name: "CompaniesComponent",
    setup() {
        const roleTypes = ref([
            'Admin',
            'Agent',
            'User',
        ]);
        const userId = ref();
        const params = ref({});
        const dataGridRef = ref(null);
        const selectedRoleType = ref("");
        const roletypedropdown = ref(false);
        const selectRoleType = (e) => {
            let value = e.itemData;
            selectedRoleType.value = value;
            params.value = { ...params.value, roleType: selectedRoleType.value };
            roletypedropdown.value = false;
        };
        const phonePattern = ref("^[0-9]{10,13}$");
        const passwordPattern = ref(/^.{7,}$/);
        const showColumn = ref(false);
        const pageSize = ref(10);
        const loadURL = `/user/categories`;
        const updateURL = `/admin/update/categories`;
        const insertURL = `/admin/categories`;
        const deleteUrl = `/admin/categories`;
        const { dataSource, refreshTable } = dxGridStore(
            loadURL,
            params,
            insertURL,
            updateURL,
            deleteUrl
        );
        console.log(dataSource);
        const saveEvent = (e) => {

            if (e.changes == 0) {
                if (params.value.roleType) {
                    // console.log(e.changes);
                    axios.post(`/admin/update/user/${userId.value}`, {
                        roleType: params.value,
                    }).then((response) => {
                        const keys = Object.keys(params.value);
                        for (let i = 1; i < keys.length; i++) {
                            delete params.value[keys[i]];
                        }
                        // console.log(params.value);
                        refreshTable(dataGridRef);
                    })

                };
            }
        }
        // console.log(dataSource);
        const initNewRow = (e) => {
           
            showColumn.value = true;
        };
        const rowInserted = (e) => {
            showColumn.value = false;
        };
        const logEvent = (e) => {
          
            userId.value = e.data.id;
            showColumn.value = false;
            
        };
        const cancelEdit = (e) => {
            showColumn.value = false;
        }
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
            selectedRoleType,
            roletypedropdown,
            params,
            logEvent,
            cancelEdit,
            saveEvent,
            userId,
            dataGridRef
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

.option>span {
    position: relative;
    top: 2px;
    margin-right: 10px;
}

.option>.dx-widget {
    display: inline-block;
    vertical-align: middle;
}

#requests .caption {
    float: left;
    padding-top: 7px;
}

#requests>div {
    padding-bottom: 5px;
}

#requests>div::after {
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