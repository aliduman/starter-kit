<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PageHero from '~corporate/PageHero.vue';
import HoneypotField from '~shared/HoneypotField.vue';

const props = defineProps({
    subjects: { type: Object, default: () => ({}) },
});

const settings = computed(() => usePage().props.settings || {});

const form = useForm({
    name: '',
    email: '',
    subject: Object.keys(props.subjects)[0] || null,
    message: '',
    consent: false,
    website: '',
});

const submit = () => form.post(route('corporate.contact.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <PageHero title="İletişim" subtitle="Bizimle iletişime geçin." />

    <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
        <div class="space-y-4">
            <div v-if="settings.contact_address" class="card">
                <h3 class="font-semibold">Adres</h3>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ settings.contact_address }}</p>
            </div>
            <div v-if="settings.contact_email" class="card">
                <h3 class="font-semibold">E-posta</h3>
                <a :href="`mailto:${settings.contact_email}`" class="mt-1 block text-sm text-slate-600">{{ settings.contact_email }}</a>
            </div>
            <div v-if="settings.contact_phone" class="card">
                <h3 class="font-semibold">Telefon</h3>
                <p class="mt-1 text-sm text-slate-600">{{ settings.contact_phone }}</p>
            </div>
        </div>

        <form class="card relative space-y-4" novalidate @submit.prevent="submit">
            <HoneypotField v-model="form.website" />

            <div>
                <label for="contact-name" class="text-sm font-medium">Adınız Soyadınız</label>
                <input id="contact-name" v-model="form.name" type="text" required autocomplete="name" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label for="contact-email" class="text-sm font-medium">E-posta Adresiniz</label>
                <input id="contact-email" v-model="form.email" type="email" required autocomplete="email" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                <p v-if="form.errors.email" class="mt-1 text-sm text-rose-600">{{ form.errors.email }}</p>
            </div>
            <div v-if="Object.keys(subjects).length">
                <label for="contact-subject" class="text-sm font-medium">Konu</label>
                <select id="contact-subject" v-model="form.subject" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    <option v-for="(label, key) in subjects" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>
            <div>
                <label for="contact-message" class="text-sm font-medium">Mesajınız</label>
                <textarea id="contact-message" v-model="form.message" rows="6" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></textarea>
                <p v-if="form.errors.message" class="mt-1 text-sm text-rose-600">{{ form.errors.message }}</p>
            </div>
            <div>
                <label class="flex items-start gap-2 text-sm">
                    <input v-model="form.consent" type="checkbox" class="mt-1">
                    <span>
                        Kişisel verilerimin
                        <a v-if="settings.privacy_url" :href="settings.privacy_url" target="_blank" rel="noopener" class="underline">aydınlatma metnine</a>
                        <template v-else>aydınlatma metnine</template>
                        uygun işlenmesini onaylıyorum.
                    </span>
                </label>
                <p v-if="form.errors.consent" class="mt-1 text-sm text-rose-600">{{ form.errors.consent }}</p>
            </div>
            <button type="submit" class="btn-primary" :disabled="form.processing">Mesajı Gönder</button>
        </form>
    </div>
</template>
