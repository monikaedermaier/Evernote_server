<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    public function index():JsonResponse{
        // load all Tags with all relations with eager loading
        $tags = Tag::with(['user', 'notes', 'todos'])->get();
        return response()->json($tags, 200); // tags sollen zurückgegeben werden + den Status 200

    }

    //load Tag with id
    public function findById(string $id):JsonResponse{
        $tag = Tag::where('id', $id)->with(['user', 'notes', 'todos'])->first();
        return $tag != null ? response()->json($tag, 200) : response()->json(null, 200);
    }

    //check Tag by ID if it already exists
    public function checkID(string $id):JsonResponse{
        $tag = Tag::where('id', $id)->first();
        return $tag != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //search methods by searchTerm
    public function findBySearchTerm(string $searchTerm):JsonResponse{
        $tags = Tag::with(['notes', 'todos'])
            ->where('title','LIKE','%'.$searchTerm.'%')
            /* Beziehung zu Todos */
            ->orWhereHas('Todos',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Notes */
            ->orWhereHas('notes',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })->get();
        return response()->json($tags, 200);
    }

    // create new tag
    public function save(Request $request):JsonResponse{
        $request = $this->parseRequest($request);
        /* start DB transaction - all in the transaction must be correctly done, otherwise error */
        DB::beginTransaction();
        try {
            $tag = Tag::create($request->all()); //create new tag

            // N:M Beziehung
            $tag->notes()->sync($request['notes']);
            $tag->save();

            // N:M Beziehung
            $tag->todos()->sync($request['todos']);
            $tag->save();

            // *:1 Beziehung
            if(isset($request['user'])){
                $us = $request['user'];
                $user = User::firstOrNew(['firstName'=>$us['firstName'],'lastName'=>$us['lastName'],'email'=>$us['email'],'password'=>$us['password']]);
                $tag->user()->save($user);
            }


            DB::commit(); // Transaktion beenden
            return response()->json($tag,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("saving tag failed".$e->getMessage(),420);
        }
    }

    // update tags
    public function update(Request $request, string $id):JsonResponse{
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $tag = Tag::with(['user', 'notes', 'todos'])->where('id', $id)->first();
            if($tag != null){
                $request = $this->parseRequest($request);
                $tag->update($request->all());

                // N:M Beziehung
                $tag->notes()->sync($request['notes']);
                $tag->save();

                // N:M Beziehung
                $tag->todos()->sync($request['todos']);
                $tag->save();

                // *:1 Beziehung
                $tag->user()->dissociate();
                if(isset($request['user_id'])){
                    $us = $request['user_id'];
                    $user = User::where("id", $us)->first();
                    $tag->user()->associate($user);
                }
            }
            DB::commit();
            $tag1 = Tag::with(['user', 'notes', 'todos'])->where('id', $id)->first();
            return response()->json($tag1,201);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json("updating tag failed".$e->getMessage(),420);
        }
    }

    // delete tags
    public function delete(string $id):JsonResponse{
        $tag = Tag::where('id', $id)->first();
        if($tag != null){
            $tag->delete();
            return response()->json('tag ('.$id.') successfully deleted',200);
        } else {
            return response()->json("could not delete tag - it does not exists",422);
        }
    }

    private function parseRequest(Request $request):Request { // Hilfsmethode: wandle eingegebenes Datum um, damit wir es im richtigen Format in die Datenbank speichern können
        // get date and covert it - it is in ISO 8601, "2024-03-22T16:29:00.000Z"
        $date = new DateTime($request->dueDate);
        $request['dueDate'] = $date->format('Y-m-d H:i:s');
        return $request;
    }
}
