<div>
    <div class="mb-6 flex items-center justify-between">
        <button type="button" class="btn btn-primary btn-sm" wire:click="create">
            <x-icon name="plus" class="icon h-4 w-4" /> New Project
        </button>
    </div>

    @if ($showForm)
        <x-admin.modal title="{{ $editingId ? 'Edit Project' : 'New Project' }}" subtitle="Showcase a piece of work on your portfolio" icon="folder" size="lg" close-method="cancel">
            <form wire:submit="save" id="project-form" class="grid gap-4 md:grid-cols-2">
                <x-admin.field label="Title" name="title" placeholder="Project name" icon="edit" required error="{{ $errors->first('title') }}" wire:model="title" />
                <x-admin.field label="Status" name="status" placeholder="e.g. Completed" icon="activity" wire:model="status" />
                <x-admin.field label="Link" name="link" placeholder="https://project.example.com" icon="link" wire:model="link" />
                <x-admin.field label="Thumbnail URL" name="thumbnail" placeholder="https://.../thumbnail.jpg" icon="image" wire:model="thumbnail" />
                <div class="md:col-span-2">
                    <x-admin.field label="Tech (comma separated)" name="tech" placeholder="laravel, livewire, mysql" icon="code" hint="Separate each technology with a comma" wire:model="tech" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.field label="Description" name="description" type="textarea" rows="4" placeholder="What this project does" wire:model="description" />
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 pb-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <input type="checkbox" wire:model="featured" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-white/20 dark:bg-white/10" /> Featured on homepage
                </label>
            </form>
            <x-slot:footer>
                <x-admin.modal-actions form-id="project-form" save-label="{{ $editingId ? 'Update project' : 'Create project' }}" />
            </x-slot:footer>
        </x-admin.modal>
    @endif

    <section class="table-shell">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Tech</th>
                    <th>Featured</th>
                    <th>Visibility</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->projects as $project)
                    <tr>
                        <td>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $project->title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($project->description, 60) }}</p>
                        </td>
                        <td><span class="badge badge-brand">{{ $project->status }}</span></td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach (($project->tech ?? []) as $t)
                                    <span class="badge badge-neutral">{{ $t }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <button type="button" wire:click="toggleFeatured({{ $project->id }})" class="badge cursor-pointer {{ $project->featured ? 'badge-brand' : 'badge-neutral' }}">
                                {{ $project->featured ? 'Yes' : 'No' }}
                            </button>
                        </td>
                        <td>
                            <select
                                wire:change="setStatus({{ $project->id }}, $event.target.value)"
                                class="input !w-auto !py-1 !pr-8 text-xs font-semibold {{ $project->isPublished() ? '!border-emerald-300/60 !text-emerald-700 dark:!text-emerald-300' : ($project->isDraft() ? '!border-amber-300/60 !text-amber-700 dark:!text-amber-300' : '!border-slate-300/60 !text-slate-600 dark:!text-slate-300') }}"
                                aria-label="Project visibility"
                                name="publish_status"
                            >
                                @foreach (\App\Models\Project::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($project->publish_status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="text-right">
                            <x-admin.actions :view="route('home').'#projects'" :edit="$project->id" :delete="$project->id" delete-message="Delete this project?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">No projects found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
