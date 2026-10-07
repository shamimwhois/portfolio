import collapse from '@alpinejs/collapse';

// Cursor-tracking spotlight glow for `.spotlight` cards.
// Event delegation on document so it survives Livewire re-renders.
document.addEventListener(
    'pointermove',
    (event) => {
        const card = event.target.closest('.spotlight');
        if (!card) return;
        const rect = card.getBoundingClientRect();
        card.style.setProperty('--spot-x', `${event.clientX - rect.left}px`);
        card.style.setProperty('--spot-y', `${event.clientY - rect.top}px`);
    },
    { passive: true }
);

document.addEventListener('alpine:init', () => {
    // Livewire bundles its own Alpine instance — register plugins on it via
    // window.Alpine so x-collapse (smooth height animation) becomes available.
    window.Alpine.plugin(collapse);

    const readStoredTheme = () => {
        try {
            return window.localStorage.getItem('theme');
        } catch (e) {
            return null;
        }
    };

    const writeStoredTheme = (value) => {
        try {
            window.localStorage.setItem('theme', value);
        } catch (e) {
            /* storage unavailable — theme applies for this session only */
        }
    };

    window.Alpine.data('themeSwitcher', () => ({
        theme: readStoredTheme() || 'system',
        open: false,

        // Must mirror \App\Livewire\Admin\Settings\Appearance::THEMES + THEME_META
        themes: [
            { name: 'system', label: 'System', accent: '#6366f1', swatch: 'linear-gradient(135deg, #f4f5fb 0%, #f4f5fb 50%, #0b0d17 50%, #0b0d17 100%)' },
            { name: 'light', label: 'Light', accent: '#6366f1', swatch: 'linear-gradient(135deg, #f4f5fb 0%, #f4f5fb 50%, #6366f1 50%, #6366f1 100%)' },
            { name: 'dark', label: 'Dark', accent: '#6366f1', swatch: 'linear-gradient(135deg, #0b0d17 0%, #0b0d17 50%, #6366f1 50%, #6366f1 100%)' },
            { name: 'light_ice', label: 'Ice', accent: '#467eab', swatch: 'linear-gradient(135deg, #eef4fa 0%, #eef4fa 50%, #467eab 50%, #467eab 100%)' },
            { name: 'light_rose', label: 'Rose', accent: '#d44d84', swatch: 'linear-gradient(135deg, #fdf3f6 0%, #fdf3f6 50%, #d44d84 50%, #d44d84 100%)' },
            { name: 'dark_aurora', label: 'Aurora', accent: '#14ae8d', swatch: 'linear-gradient(135deg, #0a1513 0%, #0a1513 50%, #14ae8d 50%, #14ae8d 100%)' },
            { name: 'dark_orchid', label: 'Orchid', accent: '#8b4fff', swatch: 'linear-gradient(135deg, #120d22 0%, #120d22 50%, #8b4fff 50%, #8b4fff 100%)' },
        ],

        init() {
            // Clamp legacy/unknown stored values (e.g. old 'dark_magenta') to a real theme.
            const names = this.themes.map((t) => t.name);
            if (names.indexOf(this.theme) === -1) this.theme = 'system';
            this.apply(this.theme);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') this.apply('system');
            });
        },

        get current() {
            return this.themes.find((t) => t.name === this.theme) ?? this.themes[0];
        },

        mode(value) {
            if (value === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            return value.startsWith('dark') ? 'dark' : 'light';
        },

        apply(value) {
            const dark = this.mode(value) === 'dark';
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.setAttribute('data-theme', value);
            writeStoredTheme(value);
        },

        select(value) {
            this.theme = value;
            this.apply(value);
            this.open = false;
        },
    }));

    window.Alpine.data('navLock', () => ({
        mobileOpen: false,
        toggle() {
            this.mobileOpen = !this.mobileOpen;
        },
        close() {
            this.mobileOpen = false;
        },
        init() {
            this.$watch('mobileOpen', (open) => {
                document.body.style.overflow = open ? 'hidden' : '';
            });
        },
        destroy() {
            document.body.style.overflow = '';
        },
    }));

    window.Alpine.data('flash', () => ({
        visible: true,
        dismiss() {
            this.visible = false;
        },
    }));

    window.Alpine.data('checkoutLock', () => ({
        init() {
            document.body.style.overflow = 'hidden';
        },
        close() {
            document.body.style.overflow = '';
            this.$wire.closeCheckout();
        },
        destroy() {
            document.body.style.overflow = '';
        },
    }));

    window.Alpine.data('mediaViewer', () => ({
        items: [],
        index: 0,
        open: false,
        openViewer(payload) {
            this.items = payload.items ?? [];
            this.index = payload.index ?? 0;
            this.open = this.items.length > 0;
            document.body.style.overflow = this.open ? 'hidden' : '';
        },
        close() {
            this.open = false;
            document.body.style.overflow = '';
        },
        next() {
            this.index = (this.index + 1) % this.items.length;
        },
        prev() {
            this.index = (this.index - 1 + this.items.length) % this.items.length;
        },
        onEscape() {
            this.close();
        },
    }));

    window.Alpine.data('modal', () => ({
        open: false,
        item: null,
        openModal(payload) {
            this.item = payload;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.open = false;
            this.item = null;
            document.body.style.overflow = '';
        },
        onEscape() {
            this.closeModal();
        },
    }));

    window.Alpine.data('adminModal', () => ({
        trigger: null,
        init() {
            this.trigger = document.activeElement;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                const target = this.$el.querySelector('input:not([type="hidden"]), select, textarea');
                if (target) target.focus({ preventScroll: true });
            });
        },
        close() {
            document.body.style.overflow = '';
            this.restoreFocus();
            const method = this.$el.dataset.closeMethod || 'cancel';
            if (typeof this.$wire?.[method] === 'function') {
                this.$wire[method]();
            }
        },
        restoreFocus() {
            if (this.trigger?.focus) this.trigger.focus({ preventScroll: true });
        },
        destroy() {
            document.body.style.overflow = '';
            this.restoreFocus();
        },
    }));
});
