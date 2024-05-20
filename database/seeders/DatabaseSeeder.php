<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Collection;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reihenfolge beachten ()
        $this->call(UsersTableSeeder::class);
        $this->call(CollectionsTableSeeder::class);
        $this->call(TodosTableSeeder::class);
        $this->call(TagsTableSeeder::class);
        $this->call(NotesTableSeeder::class);

    }
}
