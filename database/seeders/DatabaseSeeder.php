<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Production'da bilinen şifreli varsayılan hesap açılmaz: admin .env'deki SETUP_ADMIN_* ile oluşur.
        $email = config('app.setup_admin.email') ?? (app()->isProduction() ? null : 'admin@example.com');
        $password = config('app.setup_admin.password') ?? (app()->isProduction() ? null : 'password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('SETUP_ADMIN_EMAIL / SETUP_ADMIN_PASSWORD boş: admin hesabı oluşturulmadı.');

            return;
        }

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        if ($role = Role::query()->where('name', 'super-admin')->first()) {
            $admin->assignRole($role);
        }
    }
}
