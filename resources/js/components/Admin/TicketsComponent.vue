<template>
    <DxDataGrid id="grid" :remote-operations="true" :show-borders="true" :data-source="dataSource"
        :column-auto-width="true" :allow-column-resizing="true" @init-new-row="initNewRow" @row-inserted="rowInserted"
        @editing-start="logEvent" @edit-canceled="cancelEdit">
        <DxEditing :allow-adding="true" :allow-updating="true" :allow-deleting="true" :use-icons="true" mode="popup" />
        <DxSearchPanel :visible="true" />
        <DxColumn data-field="Tittle" data-type="string">
        </DxColumn>
        <DxColumn data-field="Status" data-type="string">
            <DxRequiredRule />
            <DxEmailRule message="Email is invalid" />
        </DxColumn>
        <DxColumn data-field="Priority" data-type="string">
        </DxColumn>
        <DxColumn data-field="Category" data-type="string">
        </DxColumn>
        <DxColumn data-field="Author Name" data-type="string">
        </DxColumn>
        <DxColumn data-field="Author Email" data-type="string">
        </DxColumn>
        <DxColumn data-field="Assigned to User" data-type="string">
        </DxColumn>
        <!-- <DxColumn caption="Roles" cell-template="role-template" edit-cell-template="rolesdropdown">
        </DxColumn>
        <template #rolesdropdown>
            <DxDropDownBox :accept-custom-value="true" label="Select Role" labelMode="floating"
                v-model:value="selectedRoleType" v-model:opened="roletypedropdown">
                <DxList :data-source="roleTypes" selection-mode="single" @item-click="selectRoleType" />
            </DxDropDownBox>
        </template> -->
        <template #role-template="{ data: cell }">
            <span v-if="cell && cell.data && cell.data.roles">
                <span v-for="(role, index) in cell.data.roles" :key="index">
                    {{ role.name }}
                </span>
            </span>
        </template>
        <DxColumn data-field="password" data-type="password" :visible="showColumn" v-if="showColumn">
            <DxPatternRule :pattern="passwordPattern"
                message="Should be of min. seven charcter and must contains a special character only" />
        </DxColumn>
        <DxPaging :page-size="pageSize" />
        <DxPager :visible="true" :allowed-page-sizes="[10, 15, 20]" :display-mode="'compact'"
            :show-page-size-selector="true" :show-navigation-buttons="true" :show-info="true" />
        <DxSummary>
            <DxTotalItem column="id" summary-type="count" />
        </DxSummary>
    </DxDataGrid>
</template>
<script>
import dxGridStore from "../../composition/dxGridStore";
import { ref } from "vue";
export default {
    name: "CompaniesComponent",
    setup() {

        const params = ref({});
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
        const roleTypes = ref([
            'Admin',
            'Agent',
            'User',
        ]);
        const pageSize = ref(10);
        const loadURL = `/admin/get/tickets`;
        const updateURL = `/admin/update/ticket`;
        const deleteUrl = `/admin/remove/ticket`;
        const { dataSource } = dxGridStore(
            loadURL,
            params,
            null,
            updateURL,
            deleteUrl
        );
        // console.log(dataSource);
        const initNewRow = (e) => {
            selectedRoleType.value = null
            showColumn.value = true;
        };
        const rowInserted = (e) => {
            showColumn.value = false;
        };
        const logEvent = (e) => {
            showColumn.value = false;
            selectedRoleType.value = e.data.roles[0].name;
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
            cancelEdit
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
