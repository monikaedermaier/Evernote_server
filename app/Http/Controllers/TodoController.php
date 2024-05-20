<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Note;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    public function index():JsonResponse{
        // load all Todos with all relations with eager loading
        $todos = Todo::with(['images', 'users', 'tags', 'note'])->get();
        return response()->json($todos, 200); // todos sollen zurückgegeben werden + den Status 200
    }

    //load Todo with id
    public function findById(string $id):JsonResponse{
        $todo = Todo::where('id', $id)->with(['images', 'users', 'tags', 'note'])->first();
        return $todo != null ? response()->json($todo, 200) : response()->json(null, 200);
    }

    //check Todo by ID if it already exists
    public function checkID(string $id):JsonResponse{
        $todo = Todo::where('id', $id)->first();
        return $todo != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //search methods by searchTerm
    public function findBySearchTerm(string $searchTerm):JsonResponse{
        $todos = Todo::with(['images', 'users', 'tags', 'note'])
            ->where('title','LIKE','%'.$searchTerm.'%')
            ->orWhere('description','LIKE','%'.$searchTerm.'%')
            /* Beziehung zu Users */
            ->orWhereHas('users',function ($query) use ($searchTerm){
                $query->where('firstName','LIKE','%'.$searchTerm.'%')
                    ->orWhere('lastName','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Tags */
            ->orWhereHas('tags',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Note */
            ->orWhereHas('note',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })->get();
        return response()->json($todos, 200);
    }

    // create new todo
    public function save(Request $request):JsonResponse{
        $request = $this->parseRequest($request);
        /* start DB transaction - all in the transaction must be correctly done, otherwise error */
        DB::beginTransaction();
        try {
            $todo = Todo::create($request->all()); //create new todo
            if(isset($request['images']) && is_array($request['images'])){
                foreach ($request['images'] as $img){
                    $image = Image::firstOrNew(['url'=>$img['url'],'title'=>$img['title']]); // check if Note has same Images & Title, if not create a new Note
                    $todo->images()->save($image);
                }
            }
            // N:M Beziehung
            $todo->users()->sync($request['users']);
            $todo->save();

            // N:M Beziehung
            $todo->tags()->sync($request['tags']);
            $todo->save();

            // *:1 Beziehung
            if(isset($request['note'])){
                $no = $request['note'];
                $note = Note::firstOrNew(['title'=>$no['title'],'description'=>$no['description']]);
                $todo->note()->save($note);
            }
            DB::commit(); // Transaktion beenden
            return response()->json($todo,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("saving Todo failed".$e->getMessage(),420);
        }
    }

    // update todo
    public function update(Request $request, string $id):JsonResponse{
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $todo = Todo::with(['images', 'users', 'tags', 'note'])->where('id', $id)->first();
            if($todo != null){
                $request = $this->parseRequest($request);
                $todo->update($request->all());

                // update images
                $todo->images()->delete();
                if(isset($request['images']) && is_array($request['images'])){
                    foreach ($request['images'] as $img){
                        $image = Image::firstOrNew(['url'=>$img['url'],'title'=>$img['title']]);
                        $todo->images()->save($image);
                    }
                }

                // *:1 Beziehung
                $todo->note()->dissociate();
                if(isset($request['note_id'])){
                    $not = $request['note_id'];
                    $note = Note::where("id", $not)->first();
                    $todo->note()->associate($note);
                }

                // N:M Beziehung
                $todo->users()->sync($request['users']);
                $todo->save();

                // N:M Beziehung
                $todo->tags()->sync($request['tags']);
                $todo->save();
            }
            DB::commit();
            $todo1 = Todo::with(['images', 'users', 'tags', 'note'])->where('id', $id)->first();
            return response()->json($todo1,201);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json("updating todo failed".$e->getMessage(),420);
        }
    }

    // delete todo
    public function delete(string $id):JsonResponse{
        $todo = Todo::where('id', $id)->first();
        if($todo != null){
            $todo->delete();
            return response()->json('todo ('.$id.') successfully deleted',200);
        } else {
            return response()->json("could not delete todo - it does not exists",422);
        }
    }

    private function parseRequest(Request $request):Request { // Hilfsmethode: wandle eingegebenes Datum um, damit wir es im richtigen Format in die Datenbank speichern können
        // get date and covert it - it is in ISO 8601, "2024-03-22T16:29:00.000Z"
        $date = new DateTime($request->dueDate);
        $request['dueDate'] = $date->format('Y-m-d H:i:s');
        return $request;
    }
}
