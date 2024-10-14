<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { jsonapiStore } from '@/store.js'

const parts = ref([]);
const filters = ref();
const loading = ref(true);
const page = usePage();
const selectedParts = ref([]);
const dt = ref();
const store = jsonapiStore();

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        part_type: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        manufacturer: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        model_number: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        list_price: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        is_active: { value: null, matchMode: FilterMatchMode.EQUALS }
    };

    if (page.props.auth.can.create_team_part) {
        filters.value.is_associated = { value: null, matchMode: FilterMatchMode.EQUALS }
    }
};

initFilters();

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};
const clearFilter = () => {
    initFilters();
};

const addTeamPart = function (part) {
    const newTeamPart = {
        _jv: {
            type: 'team-parts',
            relationships: {
                part : {
                    data: {
                        type: 'parts',
                        id: part.id
                    }
                }
            }
        },
    }

    store.post(
        newTeamPart,
        route('v1.team-parts.store')
    ).then(data => {
        part.isAssociated = !part.isAssociated;
        console.log(data)
    }).catch(error => console.log(errror));
}

const removeTeamPart = function (part) {
    if (confirm('Are you sure you want to proceed?\nThis will also delete the price set for the team part.')) {
        router.delete(
            route(
                'team_part.destroy',
                { team_part: part.team_part_id }
            ),
            {
                onSuccess: (page) => {
                    part.is_associated = !part.is_associated;

                    alert('Part was removed from the team.');
                },
                onError: (errors) => {
                    console.log(errors);
                },
                preserveScroll: true,
            }
        )
    }
}

const isBatchAddEnabled = computed(function () {
    if (selectedParts.value.length === 0) return false;

    return selectedParts.value.some(part => !part.is_associated);
})

const isBatchRemoveEnabled = computed(function () {
    if (selectedParts.value.length === 0) return false;

    return selectedParts.value.some(part => part.is_associated);
})

const batchAdd = (event) => {
    const partIds = selectedParts.value
        .filter(part => !part.is_associated)
        .map(part => part.id);

    router.post(
        route('team_part.store_batch'),
        { part_ids: partIds },
        {
            onSuccess: (page) => {
                selectedParts.value
                    .filter(part => partIds.includes(part.id))
                    .forEach(part => part.is_associated = !part.is_associated)

                selectedParts.value = [];

                alert('Parts added.')
            },
            onError: (errors) => {
                console.log(errors);
            },
            preserveScroll: true,
        }
    )
}

const batchRemove = (event) => {
    if (confirm('Are you sure you want to proceed?\nThis will also delete the price set for the team parts.')) {
        const teamPartIds = selectedParts.value
            .filter(part => part.is_associated)
            .map(part => part.team_part_id);

        router.post(
            route('team_part.destroy_batch'),
            { team_part_ids: teamPartIds },
            {
                onSuccess: (page) => {
                    selectedParts.value
                        .filter(part => teamPartIds.includes(part.id))
                        .forEach((part) => {
                            part.is_associated = !part.is_associated;
                            part.team_part_id = null;
                        })

                    selectedParts.value = [];

                    alert('Parts were removed from the team.')
                },
                onError: (errors) => {
                    console.log(errors);
                },
                preserveScroll: true,
            }
        )
    }
}

const exportCSV = () => {
    dt.value.exportCSV();
};

const exportFunction = ({ data, field }) => {
    switch (field) {
        case 'is_active':
            return data ? 'Y' : 'N';
        default:
            return String(data);
    }
}

const lazyParams = ref({});

onMounted(() => {
    lazyParams.value = {
        first: dt.value.first,
        rows: dt.value.rows,
        sortField: null,
        sortOrder: null,
        filters: filters.value
    };

    loadLazyData();
});

const loadLazyData = (event) => {
    loading.value = true;

    store.get('parts').then(data => {
        let parsedParts = [];

        for (const [key, value] of Object.entries(data)) {
            parsedParts.push({ id: key, ...value })
        }

        parts.value = parsedParts;

        loading.value = false;
    });
};
</script>

<template>

    <Head title="Parts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Parts
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <DataTable v-model:selection="selectedParts" v-model:filters="filters" :value="parts" paginator
                            showGridlines :rows="10" dataKey="id" filterDisplay="menu" :loading="loading"
                            :globalFilterFields="['partType', 'manufacturer', 'modelNumber']" ref="dt"
                            :export-function="exportFunction">
                            <template #header>
                                <div class="flex justify-between">
                                    <div>
                                        <Button type="button" icon="pi pi-filter-slash" label="Clear" outlined
                                            @click="clearFilter()" class="mr-1" />
                                        <Button v-if="$page.props.auth.can.create_team_part"
                                            :disabled="!isBatchAddEnabled" type="button" icon="pi pi-plus" label="Add"
                                            outlined severity="primary" @click="batchAdd($event)" class="mr-1" />
                                        <Button v-if="$page.props.auth.can.create_team_part"
                                            :disabled="!isBatchRemoveEnabled" type="button" icon="pi pi-times"
                                            label="Remove" outlined severity="danger" @click="batchRemove($event)" />
                                    </div>

                                    <div>
                                        <div class="text-end pb-4" v-if="$page.props.auth.can.download_any_part">
                                            <Button icon="pi pi-external-link" label="Export"
                                                @click="exportCSV($event)" />
                                        </div>
                                        <IconField>
                                            <InputIcon>
                                                <i class="pi pi-search" />
                                            </InputIcon>
                                            <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                                        </IconField>
                                    </div>
                                </div>
                            </template>
                            <template #empty> No parts found. </template>
                            <template #loading> Loading parts data. Please wait. </template>
                            <Column v-if="$page.props.auth.can.create_team_part" selectionMode="multiple"
                                headerStyle="width: 3rem"></Column>
                            <Column field="part_type" header="Part type" style="min-width: 12rem"
                                export-header="Part Type">
                                <template #body="{ data }">
                                    {{ data.partType }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text"
                                        placeholder="Search by part type" />
                                </template>
                            </Column>
                            <Column field="manufacturer" header="Manufacturer" style="min-width: 12rem">
                                <template #body="{ data }">
                                    {{ data.manufacturer }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column field="model_number" header="Model number" style="min-width: 12rem"
                                export-header="Model Number">
                                <template #body="{ data }">
                                    {{ data.modelNumber }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="List price" filterField="list_price" dataType="numeric"
                                style="min-width: 10rem" export-header="List Price" field="list_price">
                                <template #body="{ data }">
                                    {{ formatCurrency(data.listPrice) }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" mode="currency" currency="PHP"
                                        locale="en-US" />
                                </template>
                            </Column>
                            <Column field="isActive" header="Is active?" dataType="boolean" bodyClass="text-center"
                                style="min-width: 8rem" export-header="Active">
                                <template #body="{ data }">
                                    <i class="pi"
                                        :class="{ 'pi-check-circle text-green-500 ': data.isActive, 'pi-times-circle text-red-500': !data.isActive }"></i>
                                </template>
                                <template #filter="{ filterModel }">
                                    <label for="is-active-filter" class="font-bold"> Is Active </label>
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null"
                                        binary inputId="is-active-filter" />
                                </template>
                            </Column>
                            <Column v-if="$page.props.auth.can.create_team_part" field="isAssociated"
                                header="Added to team?" dataType="boolean" bodyClass="text-center"
                                style="min-width: 8rem">
                                <template #body="{ data }">
                                    <i class="pi"
                                        :class="{ 'pi-check-circle text-green-500 ': data.isAssociated, 'pi-times-circle text-red-500': !data.isAssociated }"></i>
                                </template>
                                <template #filter="{ filterModel }">
                                    <label for="is-associated-filter" class="font-bold"> Is Associated </label>
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null"
                                        binary inputId="is-associated-filter" />
                                </template>
                            </Column>
                            <Column header="Action" v-if="$page.props.auth.can.create_team_part">
                                <template #body="{ data }">
                                    <Button @click="removeTeamPart(data)" v-if="data.isAssociated" icon="pi pi-times"
                                        title="Remove from team parts" severity="danger"></Button>
                                    <Button @click="addTeamPart(data)" v-if="!data.isAssociated" icon="pi pi-plus"
                                        title="Add to team parts" severity="success"></Button>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
