<script setup>
import { computed } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const resendForm = useForm({
    email: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const resend = () => {
    resendForm.email = form.email;
    resendForm.post(route('verification.send.guest'));
};

const needsVerification = computed(() => {
    const error = form.errors.email;
    return error && error.includes('verificar seu e-mail');
});

const linkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />

        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Bem-vindo de volta</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Faça login para continuar</p>

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm font-medium text-green-600">
            E-mail de verificação reenviado! Verifique sua caixa de entrada.
        </div>

        <div v-else-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />

                <button
                    v-if="needsVerification"
                    type="button"
                    @click="resend"
                    :disabled="resendForm.processing"
                    class="mt-1 text-sm text-gray-900 dark:text-white underline hover:text-gray-700 dark:hover:text-gray-300 disabled:opacity-50"
                >
                    Reenviar e-mail de verificação
                </button>
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Senha" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Lembrar de mim</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 underline"
                >
                    Esqueceu sua senha?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Ainda não tem conta?
            <Link :href="route('register')" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-medium underline">
                Criar conta
            </Link>
        </p>
    </GuestLayout>
</template>
