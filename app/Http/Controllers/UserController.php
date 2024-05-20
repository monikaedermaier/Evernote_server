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

class UserController extends Controller
{
    public function index():JsonResponse{
        // load all Collections with all relations with eager loading
        $users = User::with(['images', 'collections', 'notes', 'tags', 'todos'])->get();
        return response()->json($users, 200); // users sollen zurückgegeben werden + den Status 200
    }

    //load User with id
    public function findById(string $id):JsonResponse{
        $user = User::where('id', $id)->with(['images', 'notes', 'collections', 'todos'])->first();
        return $user != null ? response()->json($user, 200) : response()->json(null, 200);
    }

    //check User by ID if he/she already exists
    public function checkID(string $id):JsonResponse{
        $user = User::where('id', $id)->first();
        return $user != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //search methods by searchTerm
    public function findBySearchTerm(string $searchTerm):JsonResponse{
        $users = User::with(['images', 'notes', 'collections', 'todos', 'tags'])
            ->where('firstName','LIKE','%'.$searchTerm.'%')
            ->orWhere('lastName','LIKE','%'.$searchTerm.'%')
            ->orWhere('email','LIKE','%'.$searchTerm.'%')
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
            /* Beziehung zu Collections */
            ->orWhereHas('collections',function ($query) use ($searchTerm){
                $query->where('name','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Tags */
            ->orWhereHas('tags',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Notes */
            ->orWhereHas('notes',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })->get();
        return response()->json($users, 200);
    }

    // create new user
    public function save(Request $request):JsonResponse{
        $request = $this->parseRequest($request);
        /* start DB transaction - all in the transaction must be correctly done, otherwise error */
        DB::beginTransaction();
        try {
            $user = User::create($request->all()); //create new user
            if(isset($request['images']) && is_array($request['images'])){
                foreach ($request['images'] as $img){
                    $image = Image::firstOrNew(['url'=>$img['url'],'title'=>$img['title']]); // check if User has same Images & Title, if not create a new Note
                    $user->images()->save($image);
                }
            }

            // 0,1:* Beziehung
            if(isset($request['notes']) && is_array($request['notes'])){
                foreach ($request['notes'] as $no){
                    $note = Note::where("id", $no)->first();
                    $user->notes()->save($note);
                }
            }

            // 1:* Beziehung
            if(isset($request['tags']) && is_array($request['tags'])){
                foreach ($request['tags'] as $ta){
                    $tag = Tag::where("id", $ta)->first();
                    $user->tags()->save($tag);
                }
            }

            // N:M Beziehung
            $user->collections()->sync($request['collections']);
            $user->save();

            // N:M Beziehung
            $user->todos()->sync($request['todos']);
            $user->save();

            DB::commit(); // Transaktion beenden
            return response()->json($user,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("saving user failed".$e->getMessage(),420);
        }
    }

    // update user
    public function update(Request $request, string $id):JsonResponse{
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $user = User::with(['images', 'notes', 'collections', 'todos', 'tags'])->where('id', $id)->first();
            if($user != null){
                $request = $this->parseRequest($request);
                $user->update($request->all());

                // update images
                $user->images()->delete();
                if(isset($request['images']) && is_array($request['images'])){
                    foreach ($request['images'] as $img){
                        $image = Image::firstOrNew(['url'=>$img['url'],'title'=>$img['title']]); // check if Note has same Images & Title, if not create a new Note
                        $user->images()->save($image);
                    }
                }

                // update notes
                $user->notes()->delete();
                if(isset($request['notes']) && is_array($request['notes'])){
                    foreach ($request['notes'] as $no){
                        $note = Note::firstOrNew(['title'=>$no['title'],'description'=>$no['description']]);
                        $user->notes()->save($note);
                    }
                }

                // update tags
                $user->tags()->delete();
                if(isset($request['tags']) && is_array($request['tags'])){
                    foreach ($request['tags'] as $ta){
                        $tag = Tag::firstOrNew(['title'=>$ta['title']]);
                        $user->tags()->save($tag);
                    }
                }

                // update collections, only to existing ones
                $collectionIds = [];
                if(isset($request['collections']) && is_array($request['collections'])){
                    foreach ($request['collections'] as $col){
                        array_push($collectionIds, $col['id']);
                    }
                }
                $user->collections()->sync($collectionIds);

                // update todos, only to existing ones
                $todosIds = [];
                if(isset($request['todos']) && is_array($request['todos'])){
                    foreach ($request['todos'] as $td){
                        array_push($todosIds, $td['id']);
                    }
                }
                $user->todos()->sync($todosIds);
                $user->save();
            }
            DB::commit();
            $user1 = User::with(['images', 'notes', 'collections', 'todos', 'tags'])->where('id', $id)->first();
            return response()->json($user1,201);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json("updating user failed".$e->getMessage(),420);
        }
    }

    // delete users
    public function delete(string $id):JsonResponse{
        $user = User::where('id', $id)->first();
        if($user != null){
            $user->delete();
            return response()->json('user ('.$id.') successfully deleted',200);
        } else {
            return response()->json("could not delete user - it does not exists",422);
        }
    }

    private function parseRequest(Request $request):Request { // Hilfsmethode: wandle eingegebenes Datum um, damit wir es im richtigen Format in die Datenbank speichern können
        // get date and covert it - it is in ISO 8601, "2024-03-22T16:29:00.000Z"
        $date = new DateTime($request->dueDate);
        $request['dueDate'] = $date->format('Y-m-d H:i:s');
        return $request;
    }
}
