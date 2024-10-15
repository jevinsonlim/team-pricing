<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { jsonapiStore } from '@/store.js'

const filters = ref();
const loading = ref(false);
const dt = ref();
const store = jsonapiStore();
const teamParts = ref([]);
const totalRecords = ref(0);
const lazyParams = ref({});
const rows = ref(5);

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        partType: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        manufacturer: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        modelNumber: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        listPrice: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        multiplier: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        staticPrice: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        teamPrice: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    };
};

initFilters();

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

const clearFilter = (event) => {
    initFilters();
    onFilter(event);
};

const exportCSV = () => {
    dt.value.exportCSV();
};

onMounted(() => {
    lazyParams.value = {
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
});

const loadLazyData = (event) => {
    loading.value = true;

    const paginationParams = {
        'page[number]': event?.page + 1 || 1,
        'page[size]': lazyParams.value.rows || rows.value
    }

    const filterParams = createFilterParams(lazyParams.value.filters);

    const searchParams = new URLSearchParams({
        ...paginationParams,
        ...filterParams
    });

    console.log(lazyParams.value.filters);

    store.search('team-parts?' + searchParams.toString())
        .then(data => {
            console.log(data);

            let parsedTeamParts = [];

            for (const [key, value] of Object.entries(data)) {
                if (key === '_jv') continue;

                parsedTeamParts.push({ id: key, ...value });
            }

            teamParts.value = parsedTeamParts;
            totalRecords.value = data._jv.json.meta.page.total;

            loading.value = false;
        });
};

const createFilterParams = (filters) => {
    let params = {};

    params['filter[team-part-filters]'] = encodeURIComponent(JSON.stringify(filters));

    return params;
}

const onPage = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onSort = (event) => {
    lazyParams.value = event;
    loadLazyData(event);
};
const onFilter = (event) => {
    lazyParams.value.filters = filters.value;
    loadLazyData(event);
};
</script>

<template>

    <Head title="Team Parts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Team Parts
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <DataTable v-model:filters="filters" :value="teamParts" paginator showGridlines :rows="rows"
                            dataKey="id" filterDisplay="menu" :loading="loading"
                            :globalFilterFields="['partType', 'manufacturer', 'modelNumber']" ref="dt" lazy
                            :totalRecords="totalRecords" @page="onPage($event)" @sort="onSort($event)"
                            @filter="onFilter($event)" :rowsPerPageOptions="[5, 10, 20, 50]">
                            <template #header>
                                <div class="flex justify-between">
                                    <div>
                                        <Button type="button" icon="pi pi-filter-slash" label="Clear" outlined
                                            @click="clearFilter($event)" class="mr-1" />
                                    </div>

                                    <div>
                                        <div class="text-end pb-4" v-if="$page.props.auth.can.download_any_team_part">
                                            <Button icon="pi pi-external-link" label="Export"
                                                @click="exportCSV($event)" />
                                        </div>
                                        <IconField>
                                            <InputIcon>
                                                <i class="pi pi-search" />
                                            </InputIcon>
                                            <InputText v-model="filters['global'].value" placeholder="Keyword Search"
                                                @change="onFilter($event)" />
                                        </IconField>
                                    </div>
                                </div>
                            </template>
                            <template #empty> No team parts found. </template>
                            <template #loading> Loading team parts data. Please wait. </template>
                            <Column header="Part type" style="min-width: 12rem" export-header="Part Type"
                                filterField="partType" exportable="true" field="partType">
                                <template #body="{ data }">
                                    {{ data.partType }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text"
                                        placeholder="Search by part type" />
                                </template>
                            </Column>
                            <Column header="Manufacturer" style="min-width: 12rem" filterField="manufacturer"
                                exportable="true" field="manufacturer">
                                <template #body="{ data }">
                                    {{ data.manufacturer }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="Model number" style="min-width: 12rem" export-header="Model Number"
                                filterField="modelNumber" exportable="true" field="modelNumber">
                                <template #body="{ data }">
                                    {{ data.modelNumber }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="List price" filterField="listPrice" dataType="numeric"
                                style="min-width: 10rem" export-header="List Price" exportable="true" field="listPrice">
                                <template #body="{ data }">
                                    {{ formatCurrency(data.listPrice) }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" mode="currency" currency="USD"
                                        locale="en-US" />
                                </template>
                            </Column>
                            <Column header="Multiplier" dataType="numeric" style="min-width: 10rem"
                                export-header="Multiplier" field="multiplier">
                                <template #body="{ data }">
                                    {{ data.multiplier }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" locale="en-US" />
                                </template>
                            </Column>
                            <Column header="Team Price" dataType="numeric" style="min-width: 10rem"
                                export-header="Team Price" field="teamPrice">
                                <template #body="{ data }">
                                    {{ data.multiplier }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" locale="en-US" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
