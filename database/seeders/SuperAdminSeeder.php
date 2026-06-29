<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = (string) env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $admin = User::query()->firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $password = env('SUPER_ADMIN_PASSWORD');

            if (blank($password) && app()->environment('production')) {
                throw new RuntimeException('SUPER_ADMIN_PASSWORD must be configured before production seeding.');
            }

            $admin->password = Hash::make((string) ($password ?: 'password'));
        }

        $admin->fill([
            'name' => (string) env('SUPER_ADMIN_NAME', 'Super Admin'),
            'role' => 'super_admin',
            'status' => true,
        ])->save();
    }
}
