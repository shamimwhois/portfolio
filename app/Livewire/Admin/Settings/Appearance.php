<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\Theme;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Appearance extends Component
{
    public string $theme_default = 'system';

    public bool $theme_switcher = true;

    public array $sections = [];

    public string $palette_theme = 'light';

    public array $palette = ['primary' => '#4663EE', 'secondary' => '#22BDFF', 'accent' => '#17A86A'];

    public const THEMES = [
        'system', 'light', 'dark',
        'light_ice', 'light_rose', 'dark_aurora', 'dark_orchid',
    ];

    /**
     * Human-readable labels + accent swatch previews for the Theme Manager.
     * Values must stay in sync with the swatches in resources/js/app.js.
     */
    public const THEME_META = [
        'system' => ['label' => 'System', 'description' => 'Follows your device', 'swatch' => 'linear-gradient(135deg, #f4f5fb 0%, #f4f5fb 50%, #0b0d17 50%, #0b0d17 100%)'],
        'light' => ['label' => 'Light', 'description' => 'Airy indigo glass', 'swatch' => 'linear-gradient(135deg, #f4f5fb 0%, #f4f5fb 50%, #6366f1 50%, #6366f1 100%)'],
        'dark' => ['label' => 'Dark', 'description' => 'Deep indigo night', 'swatch' => 'linear-gradient(135deg, #0b0d17 0%, #0b0d17 50%, #6366f1 50%, #6366f1 100%)'],
        'light_ice' => ['label' => 'Ice', 'description' => 'Cool steel blue', 'swatch' => 'linear-gradient(135deg, #eef4fa 0%, #eef4fa 50%, #467eab 50%, #467eab 100%)'],
        'light_rose' => ['label' => 'Rose', 'description' => 'Soft rose blush', 'swatch' => 'linear-gradient(135deg, #fdf3f6 0%, #fdf3f6 50%, #d44d84 50%, #d44d84 100%)'],
        'dark_aurora' => ['label' => 'Aurora', 'description' => 'Emerald teal night', 'swatch' => 'linear-gradient(135deg, #0a1513 0%, #0a1513 50%, #14ae8d 50%, #14ae8d 100%)'],
        'dark_orchid' => ['label' => 'Orchid', 'description' => 'Violet twilight', 'swatch' => 'linear-gradient(135deg, #120d22 0%, #120d22 50%, #8b4fff 50%, #8b4fff 100%)'],
    ];

    public const SECTION_KEYS = [
        'hero' => 'Hero',
        'about' => 'About',
        'stack' => 'Packages',
        'skills' => 'Skills',
        'projects' => 'Projects',
        'services' => 'Services',
        'timeline' => 'Timeline',
        'faq' => 'FAQ',
    ];

    public function mount(): void
    {
        $legacy = [
            'dark_cyan', 'dark_magenta', 'dark_violet', 'dark_emerald', 'dark_amber',
            'light_ice', 'light_slate', 'light_pearl', 'light_lavender', 'light_sand',
        ];
        $default = (string) Setting::get('theme_default', 'system');

        $this->theme_default = in_array($default, static::THEMES, true)
            ? $default
            : (str_starts_with($default, 'dark') ? 'dark' : 'light');
        $this->theme_switcher = (bool) Setting::get('theme_switcher', true);
        $this->sections = array_replace(
            array_fill_keys(array_keys(static::SECTION_KEYS), true),
            Setting::get('sections', [])
        );

        $this->palette_theme = $this->theme_default === 'system' ? 'light' : $this->theme_default;
        $this->palette = Theme::palette($this->palette_theme);
    }

    /**
     * The switcher toggle saves instantly (like Activate) so the checkbox
     * never reads as changed-but-not-persisted.
     */
    public function toggleSwitcher(): void
    {
        $this->theme_switcher = ! $this->theme_switcher;

        Setting::set('theme_switcher', $this->theme_switcher);
        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Theme switcher '.($this->theme_switcher ? 'enabled' : 'disabled').' for visitors.');
    }

    /**
     * Toggle a single homepage section on/off instantly.
     */
    public function toggleSection(string $key): void
    {
        if (! isset(static::SECTION_KEYS[$key])) {
            return;
        }

        $this->sections[$key] = ! ($this->sections[$key] ?? false);

        Setting::set('sections', $this->sections);
        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Section '.static::SECTION_KEYS[$key].' '.($this->sections[$key] ? 'shown' : 'hidden').' on the homepage.');
    }

    public function save(): void
    {
        $this->validate([
            'theme_default' => ['required', 'string', 'in:'.implode(',', static::THEMES)],
            'theme_switcher' => ['boolean'],
            'sections' => ['required', 'array'],
            'sections.*' => ['boolean'],
        ]);

        Setting::set('theme_default', $this->theme_default);
        Setting::set('theme_switcher', $this->theme_switcher);
        Setting::set('sections', $this->sections);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Settings saved.');
    }

    /**
     * Activate a theme instantly (no full-form submit) — applies site-wide
     * for first-time visitors and switches the palette editor to it.
     */
    public function activate(string $theme): void
    {
        if (! in_array($theme, static::THEMES, true)) {
            return;
        }

        $this->theme_default = $theme;
        $this->palette_theme = $theme === 'system' ? 'light' : $theme;
        $this->palette = Theme::palette($this->palette_theme);

        Setting::set('theme_default', $theme);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Theme activated — '.static::THEME_META[$theme]['label'].'.');
    }

    public function selectPaletteTheme(string $theme): void
    {
        if (! in_array($theme, static::THEMES, true)) {
            return;
        }

        $this->palette_theme = $theme;
        $this->palette = Theme::palette($theme);
    }

    public function resetPalette(): void
    {
        $defaults = Theme::defaults()[$this->palette_theme] ?? Theme::fallback();
        $this->palette = $defaults;
        $this->persistPalette();
        session()->flash('status', 'Palette reset to defaults for '.static::THEME_META[$this->palette_theme]['label'].'.');
    }

    public function savePalette(): void
    {
        $this->validate([
            'palette.primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'palette.secondary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'palette.accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $this->persistPalette();
        session()->flash('status', static::THEME_META[$this->palette_theme]['label'].' palette saved — it now re-tints the public landing.');
    }

    private function persistPalette(): void
    {
        $stored = Setting::get('theme_palettes', []);
        // Stored verbatim so a reset palette matches Theme::defaults() exactly
        // and styleTag() can skip redundant override blocks.
        $stored[$this->palette_theme] = [
            'primary' => $this->palette['primary'],
            'secondary' => $this->palette['secondary'],
            'accent' => $this->palette['accent'],
        ];

        Setting::set('theme_palettes', $stored);
        ActivityLog::record('admin.settings.updated', auth()->user());
    }

    /**
     * Theme Manager summary for the header stat panel.
     *
     * @return array{active_label: string, active_theme: string, mode: string, themes: int, sections_on: int, sections_total: int}
     */
    #[Computed]
    public function metrics(): array
    {
        $meta = static::THEME_META[$this->theme_default] ?? static::THEME_META['light'];

        $mode = match (true) {
            $this->theme_default === 'system' => 'System',
            str_starts_with($this->theme_default, 'dark') => 'Dark',
            default => 'Light',
        };

        return [
            'active_label' => $meta['label'],
            'active_theme' => $this->theme_default,
            'mode' => $mode,
            'themes' => count(static::THEMES),
            'sections_on' => collect($this->sections)->filter()->count(),
            'sections_total' => count(static::SECTION_KEYS),
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.appearance', [
            'paletteDefaults' => Theme::defaults(),
        ]);
    }
}
