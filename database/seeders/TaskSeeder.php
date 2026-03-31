<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tasks')->insert([
            [
                'title'      => 'Set up CI/CD pipeline',
                'due_date'   => now()->addDays(1)->toDateString(),
                'priority'   => 'high',
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Write unit tests for auth module',
                'due_date'   => now()->addDays(2)->toDateString(),
                'priority'   => 'high',
                'status'     => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Update API documentation',
                'due_date'   => now()->addDays(3)->toDateString(),
                'priority'   => 'medium',
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Refactor database queries',
                'due_date'   => now()->addDays(4)->toDateString(),
                'priority'   => 'medium',
                'status'     => 'done',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Clean up unused dependencies',
                'due_date'   => now()->addDays(5)->toDateString(),
                'priority'   => 'low',
                'status'     => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
