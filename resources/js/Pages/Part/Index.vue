<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';

defineProps({ parts: Array });

const filters = ref();
const loading = ref(false);
const page = usePage();
const selectedParts = ref();

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
    return value.toLocaleString('en-US', { style: 'currency', currency: 'PHP' });
};
const clearFilter = () => {
    initFilters();
};

const addTeamPart = function (part) {
    router.post(
        route('team_part.store'),
        { part_id: part.id },
        {
            onSuccess: (page) => {
                part.is_associated = !part.is_associated;
            },
            onError: (errors) => {
                console.log(errors);
            }
        }
    )
}

const removeTeamPart = function (part) {
    part.is_associated = !part.is_associated;
}
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
                            :globalFilterFields="['part_type', 'manufacturer', 'model_number']">
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
                            <template #empty> No parts found. </template>
                            <template #loading> Loading parts data. Please wait. </template>
                            <Column v-if="$page.props.auth.can.create_team_part" selectionMode="multiple"
                                headerStyle="width: 3rem"></Column>
                            <Column field="part_type" header="Part type" style="min-width: 12rem">
                                <template #body="{ data }">
                                    {{ data.part_type }}
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
                            <Column field="model_number" header="Model number" style="min-width: 12rem">
                                <template #body="{ data }">
                                    {{ data.model_number }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                                </template>
                            </Column>
                            <Column header="List price" filterField="list_price" dataType="numeric"
                                style="min-width: 10rem">
                                <template #body="{ data }">
                                    {{ formatCurrency(data.list_price) }}
                                </template>
                                <template #filter="{ filterModel }">
                                    <InputNumber v-model="filterModel.value" mode="currency" currency="PHP"
                                        locale="en-US" />
                                </template>
                            </Column>
                            <Column field="is_active" header="Is active?" dataType="boolean" bodyClass="text-center"
                                style="min-width: 8rem">
                                <template #body="{ data }">
                                    <i class="pi"
                                        :class="{ 'pi-check-circle text-green-500 ': data.is_active, 'pi-times-circle text-red-500': !data.is_active }"></i>
                                </template>
                                <template #filter="{ filterModel }">
                                    <label for="is-active-filter" class="font-bold"> Is Active </label>
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null"
                                        binary inputId="is-active-filter" />
                                </template>
                            </Column>
                            <Column v-if="$page.props.auth.can.create_team_part" field="is_associated"
                                header="Added to team?" dataType="boolean" bodyClass="text-center"
                                style="min-width: 8rem">
                                <template #body="{ data }">
                                    <i class="pi"
                                        :class="{ 'pi-check-circle text-green-500 ': data.is_associated, 'pi-times-circle text-red-500': !data.is_associated }"></i>
                                </template>
                                <template #filter="{ filterModel }">
                                    <label for="is-associated-filter" class="font-bold"> Is Associated </label>
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null"
                                        binary inputId="is-associated-filter" />
                                </template>
                            </Column>
                            <Column header="Action">
                                <template #body="{ data }">
                                    <Button @click="removeTeamPart(data)" v-if="data.is_associated" icon="pi pi-times"
                                        title="Remove from team parts" severity="danger"></Button>
                                    <Button @click="addTeamPart(data)" v-if="!data.is_associated" icon="pi pi-plus"
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
