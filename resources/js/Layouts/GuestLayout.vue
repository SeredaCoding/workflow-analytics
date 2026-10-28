<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen">
        <div class="hidden lg:flex lg:w-3/5 xl:w-3/5 relative bg-gradient-to-br from-gray-950 via-gray-900 to-indigo-950 items-center justify-center p-12 overflow-hidden">
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;" />

            <div class="relative z-10 max-w-md text-center">
                <div class="flex justify-center mb-8">
                    <ApplicationLogo class="h-40 w-auto" static-src="/images/logo.png" />
                </div>
                <p class="text-lg text-gray-400 leading-relaxed">
                    Monitore suas atividades, analise seu tempo e otimize sua produtividade.
                </p>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-950 p-6">
            <div class="w-full max-w-sm">
                <div class="lg:hidden flex flex-col items-center mb-8">
                    <Link href="/">
                        <ApplicationLogo class="h-17 w-auto mb-3" />
                    </Link>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
