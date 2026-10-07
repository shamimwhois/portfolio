@props(['align' => 'right'])

<div class="relative" x-data="themeSwitcher" x-cloak @click.outside="open = false">
    <button
        type="button"
        class="icon-btn"
        @click="open = !open"
        :title="'Theme: ' + current.label"
        :aria-label="'Change theme (' + current.label + ')'"
        :aria-expanded="open ? 'true' : 'false'"
    >
        <x-icon name="sun" class="icon h-4 w-4 hidden dark:block" />
        <x-icon name="moon" class="icon h-4 w-4 dark:hidden" />
        <span
            x-show="theme !== 'system'"
            x-cloak
            class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full ring-2 ring-white/70 dark:ring-slate-950"
            :style="'background:' + current.accent"
            aria-hidden="true"
        ></span>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="glass-strong absolute {{ $align === 'left' ? 'left-0' : 'right-0' }} z-30 mt-2 w-52 rounded-2xl p-1.5"
        role="menu"
        aria-label="Theme options"
    >
        <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Appearance</p>
        <template x-for="t in themes" :key="t.name">
            <button
                type="button"
                class="sidebar-item w-full !py-2 text-[13px]"
                :class="{ 'sidebar-item-active': theme === t.name }"
                @click="select(t.name)"
                role="menuitemradio"
                :aria-checked="theme === t.name ? 'true' : 'false'"
            >
                <span
                    class="h-4 w-4 shrink-0 rounded-full ring-1 ring-inset ring-black/10 dark:ring-white/20"
                    :style="'background:' + t.swatch"
                    aria-hidden="true"
                ></span>
                <span class="flex-1 text-left" x-text="t.label"></span>
                <x-icon name="check" class="icon h-3.5 w-3.5 opacity-0 transition-opacity" x-bind:class="theme === t.name ? 'opacity-100' : ''" />
            </button>
        </template>
    </div>
</div>
