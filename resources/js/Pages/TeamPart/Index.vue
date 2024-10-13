<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';

defineProps({ teamParts: Array });

const filters = ref();
const loading = ref(false);
const dt = ref();

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        'part.part_type': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'part.manufacturer': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'part.model_number': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
        'part.list_price': { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        multiplier: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        static_price: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
        team_price: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    };
};

initFilters();

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};
const clearFilter = () => {
    initFilters();
};

const exportCSV = () => {
    dt.value.exportCSV();
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
                        <DataTable v-model:filters="filters" :value="teamParts" paginator showGridlines :rows="10"
                            dataKey="id" filterDisplay="menu" :loading="loading"
                            :globalFilterFields="['part.part_type', 'part.manufacturer', 'part.model_number']" ref="dt">
                            <template #header>
                                <div class="flex justify-between">
                                    <div>
                                        <Button type="button" icon="pi pi-filter-slash" label="Clear" outlined
                                            @click="clearFilter()" class="mr-1" />
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
                                            <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                                        </IconField>
                                    </div>
                                </div>
                            </template>
                            <template #empty> No team parts found. </template>
                            <template #loading> Loading team parts data. Please wait. </template>
                            <Column header="Part type" style="min-width: 12rem" export-header="Part Type"
                                filterField="part.part_type" exportable="true" field="part.part_type">
                                <template #body="{ data }">
                                    {{ data.part.part_type }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text"
                                        placeholder="Search by part type" />
                                </template>
                            </Column>
                            <Column header="Manufacturer" style="min-width: 12rem" filterField="part.manufacturer"
                                exportable="true" field="part.manufacturer">
                                <template #body="{ data }">
                                    {{ data.part.manufacturer }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="Model number" style="min-width: 12rem" export-header="Model Number"
                                filterField="part.model_number" exportable="true" field="part.model_number">
                                <template #body="{ data }">
                                    {{ data.part.model_number }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="List price" filterField="part.list_price" dataType="numeric"
                                style="min-width: 10rem" export-header="List Price" exportable="true"
                                field="part.list_price" >
                                <template #body="{ data }">
                                    {{ formatCurrency(data.part.list_price) }}
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
                            <Column header="Team price" field="team_price" dataType="numeric" style="min-width: 10rem"
                                export-header="Team Price">
                                <template #body="{ data }">
                                    <span v-if="data.team_price">{{ formatCurrency(data.team_price) }}</span>
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" mode="currency" currency="USD"
                                        locale="en-US" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
