import './bootstrap';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// Iconos lineales "lucide" — se renderizan sobre <i data-lucide="name"></i>
// (lucide v0.4+ usa createIcons en vez de lucide.createIcons)
window.renderIcons = () => {
    try {
        createIcons({ icons });
    } catch (e) {
        console.warn('lucide createIcons', e);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    window.renderIcons();
});

document.addEventListener('alpine:init', () => {
    // Toast global: el store expone window.dispatch
    Alpine.store('toast', {
        items: [],
        show(message, type = 'success', title = null) {
            const id = Date.now() + Math.random();
            this.items.push({ id, type, title, message });
            setTimeout(() => {
                this.items = this.items.filter((t) => t.id !== id);
            }, 4200);
        },
        success(message, title = 'Listo') {
            this.show(message, 'success', title);
        },
        error(message, title = 'Ocurrió un problema') {
            this.show(message, 'error', title);
        },
        remove(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },
    });

    // Fecha legible en español: "vie, 7 ago 2026"
    Alpine.magic('fechaHoy', () => () => {
        const dias = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const d = new Date();
        return `${dias[d.getDay()]}, ${d.getDate()} ${meses[d.getMonth()]} ${d.getFullYear()}`;
    });

    // Desenmascara errores de validación del backend (422)
    Alpine.magic('errores', () => (errors, field) => (errors?.[field] ?? []).join(' '));
});

Alpine.start();