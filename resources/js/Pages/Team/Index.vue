<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';

defineProps({
    teams: Array
})

const filters = ref();
const loading = ref(false);

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    };
};

initFilters();

const clearFilter = () => {
    initFilters();
};
</script>

<template>

    <Head title="Teams" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Teams
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <DataTable v-model:filters="filters" :value="teams" paginator showGridlines :rows="10"
                            dataKey="id" filterDisplay="menu" :loading="loading"
                            :globalFilterFields="['name']">
                            <template #header>
                                <div class="flex justify-between">
                                    <Button type="button" icon="pi pi-filter-slash" label="Clear" outlined
                                        @click="clearFilter()" />
                                    <IconField>
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                                    </IconField>
                                </div>
                            </template>
                            <template #empty> No teams found. </template>
                            <template #loading> Loading teams data. Please wait. </template>
                            <Column field="name" header="Name" style="min-width: 12rem">
                                <template #body="{ data }">
                                    {{ data.name }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text"
                                        placeholder="Search by Name" />
                                </template>
                            </Column>
                            <Column field="members" header="Members" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <li v-for="member in data.members" style="list-style: none;">
                                        {{ member.name }}
                                    </li>
                                </template>
                            </Column>
                            <Column field="admins" header="Admins" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <li v-for="admin in data.admins" style="list-style: none;">
                                        {{ admin.name }}
                                    </li>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
