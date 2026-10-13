<script setup>
import { useForm } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cart.js';

defineProps({ gateways: { type: Array, required: true } });

const cart = useCartStore();
const form = useForm({
    gateway: 'stripe',
    amount: cart.total,
    currency: 'TRY',
});

const submit = () => form.post('/shop/checkout');
</script>

<template>

    <h1 class="text-2xl font-bold mb-6">Ödeme</h1>

    <form @submit.prevent="submit" class="card max-w-lg space-y-4">
        <div>
            <label class="block text-sm font-medium">Ödeme yöntemi</label>
            <select v-model="form.gateway" class="mt-1 w-full rounded-md border-slate-300">
                <option v-for="g in gateways" :key="g" :value="g">{{ g }}</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium">Tutar</label>
            <input v-model="form.amount" type="number" step="0.01"
                   class="mt-1 w-full rounded-md border-slate-300" />
        </div>

        <div>
            <label class="block text-sm font-medium">Para birimi</label>
            <input v-model="form.currency" maxlength="3"
                   class="mt-1 w-full rounded-md border-slate-300 uppercase" />
        </div>

        <button class="btn-primary w-full" :disabled="form.processing">Öde</button>
    </form>
</template>
