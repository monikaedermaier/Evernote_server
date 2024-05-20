<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create new user 1
        $user1 = new User();
        $user1->firstName = 'Simone';
        $user1->lastName = 'Eder';
        $user1->email = 'simone.eder@gmail.com';
        $user1->password = bcrypt('secret');

        // enter a value into the writePermission field in collection_user table
        //$collection1 = Collection::first();
        //$collection1->collections()->attach(1);

        // add users to table collection_user
        $collections = Collection::all()->pluck("id"); // get all collections - column id
        $user1->collections()->sync($collections, ['writePermission' => 1]);
        //$user->roles()->sync([1 => ['expires' => true], 2, 3]);
        //$user->roles()->syncWithPivotValues([1, 2, 3], ['active' => true]);
        $user1->save();

        // add images to user
        $image1 = new Image();
        $image1->title = "Simone Eder Profilbild";
        $image1->url = "https://cdn.businessinsider.de/wp-content/uploads/2017/04/shutterstock520346488.jpg";
        // add images to user
        $user1->images()->saveMany([$image1]);
        $user1->save();



        // create new user 2
        $user2 = new User();
        $user2->firstName = 'Lukas';
        $user2->lastName = 'Artner';
        $user2->email = 'lukas.artner@gmx.at';
        $user2->password = bcrypt('secret');
        $user2->save();
        // add images to user 2
        $image2 = new Image();
        $image2->title = "Lukas Artner Profilbild";
        $image2->url = "https://api-magazin.single.de/fileman/uploads/Neuer%20Ordner/gutes_profilbild_beispiel_2.jpg";
        $user2->save();
        // add images to user
        $user2->images()->saveMany([$image2]);
        $user2->save();


        // create new user 3
        $user3 = new User();
        $user3->firstName = 'Mona';
        $user3->lastName = 'Lisa';
        $user3->email = 'monalisa@gmx.at';
        $user3->password = bcrypt('secret');
        $user3->save();
        // add images to user 3
        $image3 = new Image();
        $image3->title = "Mona Lisa Profilbild";
        $image3->url = "https://cdn.prod.www.spiegel.de/images/7b77c446-0001-0004-0000-000000430403_w1200_r1.33_fpx50_fpy37.5.jpg";
        $user3->save();
        // add images to user
        $user3->images()->saveMany([$image3]);
        $user3->save();



    }
}
