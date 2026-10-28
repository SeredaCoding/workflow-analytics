<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-8">
            <div>
                <h2 class="text-2xl font-bold">Perfil</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie suas informações de conta e senha</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
                <UpdatePasswordForm />
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-red-200 dark:border-red-900/50 p-5 space-y-4">
                <DeleteUserForm />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})
</script>
