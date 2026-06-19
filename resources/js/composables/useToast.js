import { reactive } from 'vue';

const state = reactive({
    toasts: [],
});

let nextId = 0;

const addToast = (message, type = 'success', duration = 4000) => {
    const id = nextId++;
    state.toasts.push({ id, message, type, visible: true });

    if (duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }

    return id;
};

const removeToast = (id) => {
    const idx = state.toasts.findIndex((t) => t.id === id);
    if (idx !== -1) {
        state.toasts[idx].visible = false;
        setTimeout(() => {
            state.toasts = state.toasts.filter((t) => t.id !== id);
        }, 300);
    }
};

export function useToast() {
    return {
        toasts: state.toasts,
        success: (msg, duration) => addToast(msg, 'success', duration),
        error: (msg, duration) => addToast(msg, 'error', duration),
        info: (msg, duration) => addToast(msg, 'info', duration),
        warning: (msg, duration) => addToast(msg, 'warning', duration),
        remove: removeToast,
    };
}
