import './bootstrap';
import '../css/app.css';
import 'flowbite';

import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

// Livewire akan mem‐bootstrap Alpine dan mem-trigger event ini
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;
    if (!Alpine) return;

    Alpine.plugin(collapse);
    Alpine.plugin(focus);

    // ===== Dialog store =====
    Alpine.store('dialog', {
        open: false,
        title: '',
        message: '',
        confirmText: 'OK',
        cancelText: 'Batal',
        showCancel: true,
        resolve: null,

        confirm(opts = {}) {
            return new Promise((resolve) => {
                const o = Object.assign({
                    title: 'Konfirmasi',
                    message: 'Lanjutkan?',
                    confirmText: 'Ya',
                    cancelText: 'Batal',
                    showCancel: true,
                }, opts);

                Object.assign(this, o, { open: true, resolve });
            });
        },

        alert(opts = {}) {
            return new Promise((resolve) => {
                const o = Object.assign({
                    title: 'Informasi',
                    message: '',
                    confirmText: 'Tutup',
                    showCancel: false,
                }, opts);

                Object.assign(this, o, { open: true, resolve });
            });
        },

        _ok()     { this.open = false; this.resolve?.(true);  },
        _cancel() { this.open = false; this.resolve?.(false); },
    });

    // ===== Toast store =====
    Alpine.store('toast', {
        items: [],
        push({ type = 'info', text = '', timeout = 3500 }) {
            const id = Math.random().toString(36).slice(2, 8);
            this.items.push({ id, type, text });
            setTimeout(() => this.remove(id), timeout);
        },
        remove(id) {
            this.items = this.items.filter(t => t.id !== id);
        },
    });

        // ===== Flush flash message dari server kalau ada =====
    if (window._flash) {
        const T = Alpine.store('toast');

        if (window._flash.success) {
            T.push({ type: 'success', text: window._flash.success });
        }
        if (window._flash.error) {
            T.push({ type: 'error', text: window._flash.error });
        }
        if (window._flash.status) {
            T.push({ type: 'info', text: window._flash.status });
        }

        // bersihkan supaya tidak double kalau Alpine re-init
        window._flash = null;
    }

});
