<?php

namespace App\Console\Commands;

use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

/**
 * Creates an administrator account interactively (or via options for
 * scripted setups). The password is prompted as hidden input, validated,
 * hashed by the model cast and never echoed back.
 *
 *   php artisan admin:create
 *   php artisan admin:create --name="..." --email=... --role=super_admin   (password still prompted)
 */
class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {--name= : Display name}
        {--email= : Login e-mail (unique)}
        {--role= : Initial role (super_admin, admin, editor)}';

    protected $description = 'Create an administrator account for the admin panel';

    public function handle(): int
    {
        $roles = Role::query()->orderBy('name')->pluck('name')->all();

        if ($roles === []) {
            $this->error('No roles found. Run `php artisan db:seed` first.');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('E-mail');
        $role = $this->option('role') ?: $this->choice('Initial role', $roles, array_search('admin', $roles, true) ?: 0);
        $password = $this->secret('Password (min 12 chars, letters and numbers)');
        $confirmation = $this->secret('Confirm password');

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => strtolower(trim((string) $email)),
                'role' => $role,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'role' => ['required', 'in:'.implode(',', $roles)],
                'password' => ['required', 'confirmed', UserRequest::passwordRule()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $data = $validator->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ]);

        $user->assignRole($data['role']);

        $this->info("Administrator [{$user->email}] created with role [{$data['role']}].");

        return self::SUCCESS;
    }
}
