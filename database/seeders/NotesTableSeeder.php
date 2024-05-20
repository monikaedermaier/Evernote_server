<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Image;
use App\Models\Note;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create new note 1
        $note = new Note();
        $note->title = 'Pflanzen umtopfen';
        $note->description = 'Zwei Planzen müssen in größere Töpfe umgepflanzt werden';
        // link to user_id
        $user = User::first();
        $note->user()->associate($user);

        // link to collection_id
        $collection = Collection::first();
        $note->collection()->associate($collection);
        $note->save();

        // add images to note
        $image1 = new Image();
        $image1->title = "Bonsai";
        $image1->url = "https://th.bing.com/th/id/OIP.Ny_3LPqhtWmLIqNjUl35rgHaHa?rs=1&pid=ImgDetMain";
        $image2 = new Image();
        $image2->title = "Hängepflanze";
        $image2->url = "https://th.bing.com/th/id/R.2baa00f59f2d8bf00137c15a6afc0c1b?rik=zIHNNX91uDfAiQ&pid=ImgRaw&r=0";
        // add images to note
        $note->images()->saveMany([$image1, $image2]);

        // add tags to table note_tag
        $tagIds = [1];
        //$tags = Tag::all()->pluck("id"); // get all tags - column id
        $note->tags()->sync($tagIds);
        $note->save();

        // add todos to note
        $todo1 = new Todo();
        $todo1->title = 'Blumentopf kaufen';
        $todo1->description = 'blauer großer Blumentopf';
        $todo1->dueDate = new DateTime();
        $todo1->open = true;
        $todo2 = new Todo();
        $todo2->title = 'Dünger in die Erde geben';
        $todo2->description = 'Dünger für Grünpflanzen';
        $todo2->dueDate = new DateTime();
        $todo2->open = true;
        $note->todos()->saveMany([$todo1, $todo2]);

        // save note in database
        $note->save();



        // create new note 2
        $note2 = new Note();
        $note2->title = 'Moped muss in die Werkstatt';
        $note2->description = 'Pickerl ist im Februar fällig!';
        // link to user_id
        $user2 = User::find(3);
        $note2->user()->associate($user2);

        // link to collection_id
        $collection2 = Collection::find(2);
        $note2->collection()->associate($collection2);
        $note2->save();

        // add images to note
        $image3 = new Image();
        $image3->title = "Moped";
        $image3->url = "https://th.bing.com/th/id/OIP.dslomNB9OaxKwopBbheEkgHaFj?rs=1&pid=ImgDetMain";
        $image4 = new Image();
        $image4->title = "Werkstatt";
        $image4->url = "https://www.mallorquin-bikes.de/fileadmin/images/firmaundkontakt/_MG_6118.jpg";
        // add images to note
        $note2->images()->saveMany([$image3, $image4]);

        // add tags to table note_tag
        $tagIds = [3];
        //$tags = Tag::all()->pluck("id"); // get all tags - column id
        $note2->tags()->sync($tagIds);
        $note2->save();

        // add todos to note
        $todo3 = new Todo();
        $todo3->title = 'Moped tanken';
        $todo3->description = 'Moped hat keinen Sprit mehr, muss mit Diesel betankt werden';
        $todo3->dueDate = new DateTime();
        $todo3->open = true;
        $todo4 = new Todo();
        $todo4->title = 'Zulassungsschein suchen';
        $todo4->description = 'Zulassungsschein muss noch gesucht werden. Ist nicht in der Geldtasche';
        $todo4->dueDate = new DateTime();
        $todo4->open = true;
        // add todos to note
        $note2->todos()->saveMany([$todo3, $todo4]);

        $note2->save();
    }
}
