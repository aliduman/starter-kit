<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => form.post('/login');
</script>

<template>
    <Head title="Giriş" />

    <div class="mx-auto max-w-md">
        <div class="card">
            <h1 class="text-xl font-semibold mb-6">Giriş Yap</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">E-posta</label>
                    <input v-model="form.email" type="email" required
                           class="mt-1 w-full rounded-md border-slate-300" />
                    <p v-if="form.errors.email" class="text-sm text-rose-600 mt-1">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium">Şifre</label>
                    <input v-model="form.password" type="password" required
                           class="mt-1 w-full rounded-md border-slate-300" />
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remember" type="checkbox" /> Beni hatırla
                </label>

                <button type="submit"
                        :disabled="form.processing"
                        class="btn-primary w-full">
                    Giriş
                </button>
            </form>
        </div>
    </div>
</template>
