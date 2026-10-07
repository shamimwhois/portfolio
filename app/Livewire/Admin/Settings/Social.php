<?php

namespace App\Livewire\Admin\Settings;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\Social as SocialSupport;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Social extends Component
{
    public string $search = '';

    public array $channels = [];

    public bool $embed_enabled = false;

    public string $embed_title = '';

    public string $embed_html = '';

    public function mount(): void
    {
        foreach (SocialSupport::all() as $key => $channel) {
            $this->channels[$key] = [
                'url' => $channel['url'] ?? '',
                'enabled' => (bool) ($channel['enabled'] ?? false),
            ];
        }

        $embed = SocialSupport::embed();
        $this->embed_enabled = (bool) ($embed['enabled'] ?? false);
        $this->embed_title = (string) ($embed['title'] ?? '');
        $this->embed_html = (string) ($embed['html'] ?? '');
    }

    public function save(): void
    {
        $this->validate([
            'channels.*.url' => ['nullable', 'string', 'max:255'],
            'channels.*.enabled' => ['boolean'],
            'embed_title' => ['nullable', 'string', 'max:120'],
            'embed_html' => ['nullable', 'string', 'max:20000'],
        ]);

        $socials = [];

        foreach (config('socials.channels', []) as $key => $channel) {
            $url = trim($this->channels[$key]['url'] ?? '');
            $enabled = (bool) ($this->channels[$key]['enabled'] ?? false);

            if (filled($url)) {
                $socials[$key] = [
                    'url' => $url,
                    'enabled' => $enabled && filled($url),
                ];
            }
        }

        Setting::set('socials', $socials);
        Setting::set('socials_embed', [
            'enabled' => $this->embed_enabled,
            'title' => trim($this->embed_title),
            'html' => trim($this->embed_html),
        ]);

        ActivityLog::record('admin.settings.updated', auth()->user());
        session()->flash('status', 'Social settings saved.');
    }

    #[Computed]
    public function filteredChannels(): array
    {
        return collect(config('socials.channels', []))
            ->filter(function ($channel, string $key) {
                if (! $this->search) {
                    return true;
                }

                return str_contains(strtolower($channel['label'] ?? $key), strtolower($this->search))
                    || str_contains(strtolower($key), strtolower($this->search))
                    || str_contains(strtolower($this->channels[$key]['url'] ?? ''), strtolower($this->search));
            })
            ->all();
    }

    #[Computed]
    public function metrics(): array
    {
        $connected = 0;

        foreach ($this->channels as $channel) {
            if (! empty($channel['enabled']) && filled($channel['url'] ?? '')) {
                $connected++;
            }
        }

        return [
            'connected' => $connected,
            'total' => count(config('socials.channels', [])),
            'embed' => $this->embed_enabled && filled($this->embed_html),
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.social');
    }
}
