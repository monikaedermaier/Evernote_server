<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\User;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create new collection 1
        $collection = new Collection();
        $collection->name = 'April-Liste';
        $collection->dateOfCreation = new DateTime();
        $collection->open = true;
        $collection->save();

        // create new collection 2
        $collection = new Collection();
        $collection->name = 'Mai-Liste';
        $collection->dateOfCreation = new DateTime();
        $collection->open = false;
        $collection->save();

    }
}
