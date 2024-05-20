<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Image;
use App\Models\Note;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    // load all Notes with all relations with eager loading (eager loading = alle Daten werden sofort angezeigt und nichts nachgeladen
    public function index():JsonResponse{
        $notes = Note::with(['images', 'user', 'collection', 'todos', 'tags'])->get();
        return response()->json($notes, 200); // notes sollen zurückgegeben werden + den Status 200
    }

    //load Note with id
    public function findById(string $id):JsonResponse{
        $note = Note::where('id', $id)->with(['images', 'user', 'collection', 'todos', 'tags'])->first();
        return $note != null ? response()->json($note, 200) : response()->json(null, 200);
    }

    //check Note by ID if it already exists
    public function checkID(string $id):JsonResponse{
        $note = Note::where('id', $id)->first();
        return $note != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //search methods by searchTerm
    public function findBySearchTerm(string $searchTerm):JsonResponse{
        $notes = Note::with(['images', 'user', 'collection', 'todos', 'tags'])
            ->where('title','LIKE','%'.$searchTerm.'%')
            ->orWhere('description','LIKE','%'.$searchTerm.'%')
            /* Beziehung zu Images */
            ->orWhereHas('images',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('url','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Todos */
            ->orWhereHas('todos',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Collection */
            ->orWhereHas('collection',function ($query) use ($searchTerm){
                $query->where('name','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Tags */
            ->orWhereHas('tags',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu User */
            ->orWhereHas('user',function ($query) use ($searchTerm){
                $query->where('firstName','LIKE','%'.$searchTerm.'%')
                    ->orWhere('lastName','LIKE','%'.$searchTerm.'%');
            })->get();
        return response()->json($notes, 200);
    }

    // create new note
    public function save(Request $request):JsonResponse{
        $request = $this->parseRequest($request);
        /* start DB transaction - all in the transaction must be correctly done, otherwise error */
        DB::beginTransaction();
        try {
            $note = Note::create($request->all()); //create new note
            if(isset($request['images']) && is_array($request['images'])){
                foreach ($request['images'] as $img){
                    $image = Image::firstOrNew(['url'=>$img['url'],'title'=>$img['title']]); // check if Note has same Images & Title, if not create a new Note
                    $note->images()->save($image);
                }
            }
            // *:1 Beziehung
            if(isset($request['collection'])){
                $col = $request['collection'];
                $collection = Collection::firstOrNew(['name'=>$col['name'],'dateOfCreation'=>$col['dateOfCreation'],'open'=>$col['open']]);
                $note->collection()->save($collection);
            }

            // *:1 Beziehung
            if(isset($request['user'])){
                $us = $request['user'];
                $user = User::firstOrNew(['firstName'=>$us['firstName'],'lastName'=>$us['lastName'],'email'=>$us['email'],'password'=>$us['password']]);
                $note->user()->save($user);
            }

            // 0,1:* Beziehung
            if(isset($request['todos']) && is_array($request['todos'])){
                foreach ($request['todos'] as $td){
                    $todo = Todo::where("id", $td)->first();
                    $note->todos()->save($todo);
                }
            }

            // N:M Beziehung
            $note->tags()->sync($request['tags']);
            $note->save();

            DB::commit(); // Transaktion beenden
            return response()->json($note,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("saving note failed".$e->getMessage(),420);
        }
    }

    // update note
    public function update(Request $request, string $id):JsonResponse{
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $note = Note::with('images', 'user', 'collection', 'todos', 'tags')->where('id', $id)->first(); // find note with id
            if($note != null){
                $request = $this->parseRequest($request); // change date format for server
                $note->update($request->all());

                // update images
                $note->images()->delete();
                if(isset($request['images']) && is_array($request['images'])){
                    foreach ($request['images'] as $img){
                        $image = Image::firstOrCreate(['url'=>$img['url'],'title'=>$img['title']]); // check if Note has same Images & Title, if not create a new Note
                        $note->images()->save($image);
                    }
                }

                // *:1 Beziehung
                $note->collection()->dissociate();
                if(isset($request['collection_id'])){
                    $col = $request['collection_id'];
                    $collection = Collection::where("id", $col)->first();
                    $note->collection()->associate($collection);
                }


                // *:1 Beziehung
                $note->user()->dissociate();
                if(isset($request['user_id'])){
                    $us = $request['user_id'];
                    $user = User::where("id", $us)->first();
                    $note->user()->associate($user);
                }


                // 0,1:* Beziehung
                $note->todos()->update(['note_id' => null]);
                if(isset($request['todos']) && is_array($request['todos'])){
                    foreach ($request['todos'] as $t){
                        $todo = Todo::firstOrCreate(['id'=>$t['id']], ['title'=>$t['title'], 'description'=>$t['description'], 'dueDate'=>$t['dueDate'], 'open'=>$t['open']]);
                        $note->todos()->save($todo);
                    }
                }

                // N:M Beziehung
                $note->tags()->sync($request['tags']);
                $note->save();

            }
            DB::commit(); // end DB transaktion
            $note1 = Note::with('images', 'user', 'collection', 'todos', 'tags')->where('id', $id)->first();
            return response()->json($note1,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("updating note failed".$e->getMessage(),420);
        }
    }


    // delete note
    public function delete(string $id):JsonResponse{
        $note = Note::where('id', $id)->first();
        if($note != null){
            $note->delete();
            return response()->json('note ('.$id.') successfully deleted',200);
        } else {
            return response()->json("could not delete note - it does not exists",422);
        }
    }

    private function parseRequest(Request $request):Request {
        // get date and covert it - it is in ISO 8601, "2024-03-22T16:29:00.000Z"
        $date = new DateTime($request->dueDate);
        $request['dueDate'] = $date->format('Y-m-d H:i:s');
        return $request;
    }

}
