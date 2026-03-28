<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@pnb.ac.id';

        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'Admin Kaprodi';
        $user->role = 'admin';
        $user->nip_nim = '00000000';
        $user->password = Hash::make('admin123');
        if (! $user->exists) {
            $user->created_at = now();
        }
        $user->updated_at = now();
        $user->save();
    }
}
