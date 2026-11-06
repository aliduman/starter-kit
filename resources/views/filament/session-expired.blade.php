{{-- Oturum (CSRF) süresi dolduğunda Livewire'ın sabit İngilizce onayı yerine çevrilen metni gösterir. --}}
<script>
    (() => {
        let shown = false;
        const register = () => window.Livewire.interceptRequest(({ onError }) => {
            onError(({ response, preventDefault }) => {
                if (response.status !== 419) return;

                preventDefault();

                if (shown) return;
                shown = true;

                confirm(@js(__('This page has expired. Would you like to refresh the page?'))) && window.location.reload();
            });
        });

        window.Livewire ? register() : document.addEventListener('livewire:init', register);
    })();
</script>
