<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Note;
use App\Models\User;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index():JsonResponse{
        // load all Collections with all relations with eager loading
        $collections = Collection::with(['users', 'notes'])->get();
        return response()->json($collections, 200); // collections sollen zurückgegeben werden + den Status 200
    }

    //load Collection with id
    public function findById(string $id):JsonResponse{
        $collection = Collection::where('id', $id)->with(['users', 'notes'])->first();
        return $collection != null ? response()->json($collection, 200) : response()->json(null, 200);
    }

    //check collection by ID if it already exists
    public function checkID(string $id):JsonResponse{
        $collection = Collection::where('id', $id)->first();
        return $collection != null ? response()->json(true, 200) : response()->json(false, 200);
    }

    //search methods by searchTerm
    public function findBySearchTerm(string $searchTerm):JsonResponse{
        $collections = Collection::with(['notes', 'users'])
            ->where('name','LIKE','%'.$searchTerm.'%')
            ->orWhere('open','LIKE','%'.$searchTerm.'%')
            /* Beziehung zu Users */
            ->orWhereHas('users',function ($query) use ($searchTerm){
                $query->where('firstName','LIKE','%'.$searchTerm.'%')
                    ->orWhere('lastName','LIKE','%'.$searchTerm.'%');
            })
            /* Beziehung zu Notes */
            ->orWhereHas('notes',function ($query) use ($searchTerm){
                $query->where('title','LIKE','%'.$searchTerm.'%')
                    ->orWhere('description','LIKE','%'.$searchTerm.'%');
            })->get();
        return response()->json($collections, 200);
    }

    // create new collection
    public function save(Request $request):JsonResponse{
        $request = $this->parseRequest($request);
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $collection = Collection::create($request->all()); //create new collection

            // N:M Beziehung
            $collection->users()->sync($request['users']);
            $collection->save();

            // 1:* Beziehung
            if(isset($request['notes']) && is_array($request['notes'])){
                foreach ($request['notes'] as $no){
                    $note = Note::where("id", $no)->first();
                    $collection->notes()->save($note);
                }
            }
            DB::commit(); // Transaktion beenden
            return response()->json($collection,201);

        } catch(\Exception $e){
            DB::rollBack(); // alles was mit try gemacht wurde, wieder rückgängig machen
            return response()->json("saving collection failed".$e->getMessage(),420);
        }
    }

    // update collection
    public function update(Request $request, string $id):JsonResponse{
        /* start DB transaction */
        DB::beginTransaction();
        try {
            $collection = Collection::with(['users', 'notes'])->where('id', $id)->first();
            if($collection != null){
                $request = $this->parseRequest($request);
                $collection->update($request->all());

                // 1:* Beziehung
                $collection->notes()->update(['collection_id' => null]);
                if(isset($request['notes']) && is_array($request['notes'])){
                    foreach ($request['notes'] as $no){
                        $note = Note::firstOrCreate(['title'=>$no['title'],'description'=>$no['description'], 'user_id'=>$no['user_id']]);
                        $collection->notes()->save($note);
                    }
                }

                // N:M Beziehung
                $collection->users()->sync($request['users']);
                $collection->save();
            }
            DB::commit();
            $collection1 = Collection::with(['users', 'notes'])->where('id', $id)->first();
            return response()->json($collection1,201);

        } catch(\Exception $e){
            DB::rollBack();
            return response()->json("updating collection failed".$e->getMessage(),420);
        }
    }

    // delete collection
    public function delete(string $id):JsonResponse{
        $collection = Collection::where('id', $id)->first();
        if($collection != null){
            $collection->delete();
            return response()->json('collection ('.$id.') successfully deleted',200);
        } else {
            return response()->json("could not delete collection - it does not exists",422);
        }
    }

    private function parseRequest(Request $request):Request { // Hilfsmethode: wandle eingegebenes Datum um, damit wir es im richtigen Format in die Datenbank speichern können
        // get date and covert it - it is in ISO 8601, "2024-03-22T16:29:00.000Z"
        $date = new DateTime($request->dateOfCreation);
        $request['dateOfCreation'] = $date->format('Y-m-d H:i:s');
        return $request;
    }

}
