<script>
    async function fetchClients() {
        try {
            let data = @json($clients);
            let eventCode = @json($event->code);
            localStorage.setItem(`clients`, JSON.stringify(data));
            console.log(`Fetch data ${data.length} clients to cache successfully`);
        } catch (error) {
            console.log(error);
        }
    }

    fetchClients();

    // Cache the image
    // if ('serviceWorker' in navigator && 'caches' in window) {
    //     caches.open('image-cache').then(cache => {
    //         cache.add(imageUrl).catch(err => console.error('Image caching failed:', err));
    //     });
    // }
</script>
