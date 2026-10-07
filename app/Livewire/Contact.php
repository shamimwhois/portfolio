<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.hud')]
class Contact extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    public function mount(): void
    {
        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;
            $this->phone = auth()->user()->phone ?? '';
        }
    }

    public function submit(): void
    {
        $this->validate();

        ContactMessage::query()->create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'subject' => $this->subject ?: null,
            'message' => $this->message,
            'is_read' => false,
        ]);

        $this->sent = true;
        $this->reset('phone', 'subject', 'message');
    }

    public function render()
    {
        return view('livewire.contact', [
            'email' => Setting::get('email', 'deendayalan.pv@example.com'),
            'phone' => Setting::get('phone', '+91 90000 00000'),
        ]);
    }
}
