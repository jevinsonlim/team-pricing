<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const form = useForm({
    upload_file: null,
});

const submit = () => {
    form.post(route('part_upload.store'));
};
</script>

<template>

    <Head title="Parts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Part Uploads
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <section class="max-w-xl">
                            <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
                                <div>
                                    <InputLabel for="upload_file" value="Upload File" />

                                    <input class="mt-1 block w-full" type="file"
                                        @input="form.upload_file = $event.target.files[0]" required accept="text/csv" />

                                    <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                                        {{ form.progress.percentage }}%
                                    </progress>

                                    <InputError class="mt-2" :message="form.errors.upload_file" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing">
                                        Save
                                    </PrimaryButton>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>