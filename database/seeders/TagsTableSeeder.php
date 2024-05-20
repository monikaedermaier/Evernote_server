<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create new tag
        $tag = new Tag();
        $tag->title = 'Wohnung';
        // link to user_id
        $user = User::first();
        $tag->user()->associate($user);
        // save new tag in database
        $tag->save();

        // create new tag 2
        $tag2 = new Tag();
        $tag2->title = 'FH';
        $user = User::first();
        $tag2->user()->associate($user);
        $tag2->save();

        // create new tag 3
        $tag3 = new Tag();
        $tag3->title = 'Werkstatt';
        $user = User::find(2);
        $tag3->user()->associate($user);
        $tag3->save();

        // create new tag 4
        $tag4 = new Tag();
        $tag4->title = 'Familie';
        $user = User::first();
        $tag4->user()->associate($user);
        $tag4->save();

        // create new tag 5
        $tag5 = new Tag();
        $tag5->title = 'Geburtstag';
        $user = User::find(2);
        $tag5->user()->associate($user);
        $tag5->save();
    }
}
