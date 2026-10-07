<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\Social as SocialSupport;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Site extends Component
{
    public string $site_name = '';

    public string $site_tagline = '';

    public string $hero_kicker = '';

    public string $hero_title = '';

    public string $hero_text = '';

    public string $about_text = '';

    public string $typewriter_lines = '';

    public string $stats_json = '';

    public string $email = '';

    public string $phone = '';

    public string $facebook = '';

    public string $github = '';

    public string $telegram = '';

    public string $whatsapp = '';

    public function mount(): void
    {
        $this->site_name = (string) Setting::get('site_name', '');
        $this->site_tagline = (string) Setting::get('site_tagline', '');
        $this->hero_kicker = (string) Setting::get('hero_kicker', '');
        $this->hero_title = (string) Setting::get('hero_title', '');
        $this->hero_text = (string) Setting::get('hero_text', '');
        $this->about_text = (string) Setting::get('about_text', '');
        $this->email = (string) Setting::get('email', '');
        $this->phone = (string) Setting::get('phone', '');
        $this->facebook = (string) Setting::get('facebook', '');
        $this->github = (string) Setting::get('github', '');
        $this->telegram = (string) Setting::get('telegram', '');
        $this->whatsapp = (string) Setting::get('whatsapp', '');
        $this->typewriter_lines = implode("\n", Setting::get('typewriter_lines', []));
        $this->stats_json = json_encode(Setting::get('stats', []), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function save(): void
    {
        $this->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'stats_json' => ['nullable', 'string'],
        ]);

        $stats = json_decode($this->stats_json, true);

        if ($stats === null && trim($this->stats_json) !== '') {
            $this->addError('stats_json', 'Stats must be valid JSON.');

            return;
        }

        Setting::set('site_name', $this->site_name);
        Setting::set('site_tagline', $this->site_tagline);
        Setting::set('hero_kicker', $this->hero_kicker);
        Setting::set('hero_title', $this->hero_title);
        Setting::set('hero_text', $this->hero_text);
        Setting::set('about_text', $this->about_text);
        Setting::set('email', $this->email);
        Setting::set('phone', $this->phone);
        Setting::set('facebook', $this->facebook);
        Setting::set('github', $this->github);
        Setting::set('telegram', $this->telegram);
        Setting::set('whatsapp', $this->whatsapp);
        Setting::set('typewriter_lines', collect(explode("\n", $this->typewriter_lines))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all());
        Setting::set('stats', $stats ?? []);

        // Keep the legacy flat keys and the socials array in sync — the
        // Social settings page is the richer editor, but both must agree.
        $socials = [];

        foreach (['facebook', 'github', 'telegram', 'whatsapp'] as $key) {
            $value = trim((string) $this->{$key});

            if (filled($value)) {
                $socials[$key] = ['url' => $value, 'enabled' => true];
            }
        }

        Setting::set('socials', array_replace(Setting::get('socials', []), $socials));

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Settings saved.');
    }

    /**
     * Overview summary for the settings header — how much of the site
     * identity is filled in, how many social channels are connected, and
     * how many hero lines / stats entries are configured.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function metrics(): array
    {
        $identity = collect([$this->site_name, $this->site_tagline, $this->hero_title, $this->about_text])
            ->filter(fn (string $value) => filled($value))
            ->count();

        $social = SocialSupport::connectedCount();

        $lines = collect(explode("\n", $this->typewriter_lines))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->count();

        $stats = collect(json_decode($this->stats_json, true) ?: [])
            ->filter(fn (array $entry) => filled($entry['value'] ?? null))
            ->count();

        return [
            'identity' => $identity,
            'social' => $social,
            'lines' => $lines,
            'stats' => $stats,
        ];
    }
}
