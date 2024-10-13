<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';

defineProps({
    teamPartUploads: Array
})

const filters = ref();
const loading = ref(false);
const statuses = ref(['Pending', 'Processing', 'Processed']);

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        is_successful: { value: null, matchMode: FilterMatchMode.EQUALS },
        process_status: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    };
};

initFilters();

const clearFilter = () => {
    initFilters();
};

const getSeverity = (status) => {
    switch (status) {
        case 'Processed':
            return 'success';

        case 'Processing':
            return 'info';

        case 'Pending':
            return 'secondary';
    }
};
</script>

<template>

    <Head title="Parts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Part Uploads
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <DataTable v-model:filters="filters" :value="teamPartUploads" paginator showGridlines :rows="10"
                            dataKey="id" filterDisplay="menu" :loading="loading" :globalFilterFields="['filename']">
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
                            <template #empty> No part uploads found. </template>
                            <template #loading> Loading part uploads data. Please wait. </template>
                            <Column field="id" header="ID" style="min-width: 12rem" sortable>
                                <template #body="{ data }">
                                    {{ data.id }}
                                </template>
                            </Column>
                            <Column field="filename" header="Filename" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <Button as="a" link :label="data.filename" :href="data.upload_file_url"
                                        target="_blank" download />
                                </template>
                            </Column>
                            <Column field="created_at" header="Created at" style="min-width: 12rem" sortable>
                                <template #body="{ data }">
                                    {{ data.created_at }}
                                </template>
                            </Column>
                            <Column header="Status" field="process_status" :filterMenuStyle="{ width: '14rem' }"
                                style="min-width: 12rem">
                                <template #body="{ data }">
                                    <Tag :value="data.process_status" :severity="getSeverity(data.process_status)" />
                                </template>
                                <template #filter="{ filterModel }">
                                    <Select v-model="filterModel.value" :options="statuses" placeholder="Select One"
                                        showClear>
                                        <template #option="slotProps">
                                            <Tag :value="slotProps.option" :severity="getSeverity(slotProps.option)" />
                                        </template>
                                    </Select>
                                </template>
                            </Column>
                            <Column field="is_successful" header="Is successful?" dataType="boolean"
                                bodyClass="text-center" style="min-width: 8rem">
                                <template #body="{ data }">
                                    <i v-if="data.process_status === 'Processed'" class="pi" :class="{
                                        'pi-check-circle text-green-500 ': data.is_successful,
                                        'pi-times-circle text-red-500': !data.is_successful
                                    }">
                                    </i>
                                </template>
                                <template #filter="{ filterModel }">
                                    <label for="is-successful-filter" class="font-bold"> Is successful </label>
                                    <Checkbox v-model="filterModel.value" :indeterminate="filterModel.value === null"
                                        binary inputId="is-successful-filter" />
                                </template>
                            </Column>
                            <Column field="remarks" header="Remarks" style="min-width: 12rem">
                                <template #body="{ data }">
                                    <span v-if="data.error_message && data.remarks_file_url">
                                        <Button as="a" link :label="data.error_message" :href="data.remarks_file_url"
                                            target="_blank" download icon="pi pi-download"/>
                                    </span>
                                    
                                    <span v-if="data.error_message && !data.remarks_file_url">
                                        {{ data.error_message }}
                                    </span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
