<form wire:submit="save" id="client-project-form" class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-admin.field label="Title" name="title" placeholder="e.g. E-commerce Platform Build" required error="{{ $errors->first('title') }}" wire:model="title" />

        <div>
            <label for="userId" class="label">Customer <span class="text-red-500">*</span></label>
            <select id="userId" name="userId" wire:model="userId" class="input">
                <option value="">Select a customer...</option>
                @foreach ($this->customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                @endforeach
            </select>
            @error('userId') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="invoiceId" class="label">Linked invoice</label>
            <select id="invoiceId" name="invoiceId" wire:model="invoiceId" class="input">
                <option value="">No invoice</option>
                @foreach ($this->customerInvoices as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->invoice_no }} — {{ $inv->itemLabel() }} ({{ ucfirst($inv->status) }})</option>
                @endforeach
            </select>
            @error('invoiceId') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="label">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" wire:model="status" class="input">
                @foreach (\App\Models\ClientProject::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <x-admin.field label="Start date" name="start_date" type="date" wire:model="start_date" />
        <x-admin.field label="Deadline" name="deadline" type="date" error="{{ $errors->first('deadline') }}" wire:model="deadline" />
        <x-admin.field label="Remind at" name="remind_at" type="datetime-local" error="{{ $errors->first('remind_at') }}" wire:model="remind_at" />

        <div class="md:col-span-2">
            <x-admin.field label="Description" name="description" type="textarea" rows="3" placeholder="Short summary the customer sees" wire:model="description" />
        </div>
        <div class="md:col-span-2">
            <x-admin.field label="Requirements" name="requirements" type="textarea" rows="4" placeholder="What was agreed to be delivered — shown to the customer" wire:model="requirements" />
        </div>
        <div class="md:col-span-2">
            <x-admin.field label="Agreement" name="agreement" type="textarea" rows="4" placeholder="Terms both parties agreed to" wire:model="agreement" />
        </div>
        <div class="md:col-span-2">
            <x-admin.field label="Policy" name="policy" type="textarea" rows="4" placeholder="General project policy" wire:model="policy" />
        </div>
        <div class="md:col-span-2">
            <x-admin.field label="Cancellation policy" name="cancel_policy" type="textarea" rows="3" placeholder="What happens if the order is cancelled" wire:model="cancel_policy" />
        </div>
        <div class="md:col-span-2">
            <x-admin.field label="Return / refund policy" name="return_policy" type="textarea" rows="3" placeholder="Refund and return terms" wire:model="return_policy" />
        </div>
    </div>

</form>
