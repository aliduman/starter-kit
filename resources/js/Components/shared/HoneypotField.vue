<script setup>
// Bal küpü (App\Http\Middleware\RejectHoneypot): ekran okuyucudan ve klavyeden gizli, insanlar boş bırakır.
const model = defineModel({ type: String, default: '' });
</script>

<template>
    <div aria-hidden="true" class="absolute -left-[9999px] h-px w-px overflow-hidden">
        <label>Web sitesi <input v-model="model" type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
</template>
