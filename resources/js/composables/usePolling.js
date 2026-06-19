import { onMounted, onUnmounted } from 'vue';

export function usePolling(callback, interval = 15000) {
    let timer = null;
    onMounted(() => { timer = window.setInterval(callback, interval); });
    onUnmounted(() => { if (timer) window.clearInterval(timer); });
}
