<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     *
     * Idempotent: users are matched by email and updated in place, so this
     * can be re-run safely at any time.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'MD SHAMIM HOSSAIN',
                'email' => 'admin@scifi-hud.test',
                'role' => User::ROLE_ADMIN,
                'phone' => '+91 90000 00000',
                'bio' => 'Senior Laravel and PHP Specialist, Software Architect Engineer delivering high-performance systems with futuristic design precision.',
                'is_active' => true,
            ],
            [
                'name' => 'Ops Sentinel',
                'email' => 'ops@scifi-hud.test',
                'role' => User::ROLE_ADMIN,
                'phone' => '+91 90000 00001',
                'bio' => 'Operations lead keeping the command center online around the clock.',
                'is_active' => true,
            ],
            [
                'name' => 'Neon Client',
                'email' => 'client@scifi-hud.test',
                'role' => User::ROLE_CUSTOMER,
                'phone' => '+91 80000 00000',
                'bio' => 'Entrepreneur running a creative agency.',
                'is_active' => true,
            ],
            [
                'name' => 'Alex Rivera',
                'email' => 'alex@scifi-hud.test',
                'role' => User::ROLE_CUSTOMER,
                'phone' => '+91 80000 00001',
                'bio' => 'Startup founder scaling a SaaS platform.',
                'is_active' => true,
            ],
            [
                'name' => 'Maria Chen',
                'email' => 'maria@scifi-hud.test',
                'role' => User::ROLE_CUSTOMER,
                'phone' => '+91 80000 00002',
                'bio' => 'Marketing director at a fintech company. Deactivated to demonstrate the account gate.',
                'is_active' => false,
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password']
            );
        }

        $admin = User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'system.seeded',
            'details' => ['message' => 'Users seeded successfully.'],
        ]);

        $this->command?->info('Users seeded: admins (admin, ops) / customers (client, alex) / inactive (maria) — password: password');
    }
}
