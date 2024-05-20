<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Note;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TodosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create new todo
        $todo = new Todo();
        $todo->title = 'Blumenerde kaufen';
        $todo->description = 'zum Obi fahren und Blumenerde kaufen';
        $todo->dueDate = new DateTime();
        $todo->open = true;
        // save new todo in database
        $todo->save();

        // link to note_id
        $note = Note::first();
        $todo->note()->associate($note);
        $todo->save();

        // add images to todo
        $image3 = new Image();
        $image3->title = "Obi";
        $image3->url = "https://th.bing.com/th/id/R.7992018217b279ad2c5751fbe9b4e853?rik=kifFGAOuuNAYRQ&pid=ImgRaw&r=0";
        $image4 = new Image();
        $image4->title = "Blumenerde";
        $image4->url = "https://th.bing.com/th/id/OIP.Mymvjng8d9Vdh_ZYdmUSUAHaEK?rs=1&pid=ImgDetMain";
        $todo->images()->saveMany([$image4, $image3]);
        $todo->save();

        // add users to table todo_user
        $users = User::all()->pluck("id"); // get all users - column id
        $todo->users()->sync($users);
        $todo->save();

        // add tags to table tag_todo
        $tags = Tag::all()->pluck("id"); // get all tags - column id
        $todo->tags()->sync($tags);

        // save todo in database
        $todo->save();
    }
}
