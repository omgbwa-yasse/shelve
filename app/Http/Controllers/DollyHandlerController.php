<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Dolly;
use Illuminate\Support\Facades\Auth;




class DollyHandlerController extends Controller
{
    public function list(Request $request)  : JsonResponse
    {
        try {
            $request->validate([
                'category' => 'required|string|in:mail,communication,building,transferring,room,record,slip,container,shelf,digital_folder,digital_document,artifact'
            ]);

            // Vérifier que l'utilisateur est connecté
            if (!Auth::check() || !Auth::user()->current_organisation_id) {
                return response()->json(['dollies' => []], 200);
            }

            $dollies = Dolly::where('category', $request->category)
                ->where(function ($query) {
                    $query->where('owner_organisation_id', Auth::user()->current_organisation_id)
                          ->orWhere('is_public', true);
                })
                ->get();
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une liste vide plutôt qu'une erreur
            return response()->json(['dollies' => []], 200);
        }

        // Retourner une liste vide au lieu d'une erreur 404
        if(count($dollies) == 0){
            return response()->json(['dollies' => []], 200);
        }

        if($request->category == 'shelve'){
            $dollies->load('shelve');
        }else{
            $relation = $request->category . 's';
            $dollies->load($relation);
        }

        return response()->json(['dollies' => $dollies], 200);
    }




    public function addDolly(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:70',
            'description' => 'nullable|string|max:100',
            'category' => 'required|string|in:mail,transaction,record,slip,building,shelf,container,communication,room,digital_folder,digital_document,artifact',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::user()->id;
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;

        $dolly = Dolly::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Dolly created successfully',
            'data' => $dolly
        ]);
    }




    public function addItems(Request $request) : JsonResponse
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf,digital_folder,digital_document,artifact',
            'items' => 'required|array',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{
            if($dolly->owner_organisation_id != Auth::user()->current_organisation_id){
                return response()->json(['message' => 'Unauthorized access to this dolly'], 403);
            }
            switch($request->category) {
                case 'mail':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->mails()->where('mail_id', $item)->exists()) {
                            $dolly->mails()->attach($item);
                        }
                    }
                    break;


                case 'communication':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->communications()->where('communication_id', $item)->exists()) {
                            $dolly->communications()->attach($item);
                        }
                    }
                    break;


                case 'building':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->buildings()->where('building_id', $item)->exists()) {
                            $dolly->buildings()->attach($item);
                        }
                    }
                    break;



                case 'room':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->rooms()->where('room_id', $item)->exists()) {
                            $dolly->rooms()->attach($item);
                        }
                    }
                    break;



                case 'record':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->records()->where('record_id', $item)->exists()) {
                            $dolly->records()->attach($item);
                        }
                    }
                    break;


                case 'slip':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->slips()->where('slip_id', $item)->exists()) {
                            $dolly->slips()->attach($item);
                        }
                    }
                    break;



                case 'shelf':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->shelves()->where('shelf_id', $item)->exists()) {
                            $dolly->shelves()->attach($item);
                        }
                    }
                    break;



                case 'container':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->containers()->where('container_id', $item)->exists()) {
                            $dolly->containers()->attach($item);
                        }
                    }
                    break;

                case 'digital_folder':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->digitalFolders()->where('folder_id', $item)->exists()) {
                            $dolly->digitalFolders()->attach($item);
                        }
                    }
                    break;

                case 'digital_document':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->digitalDocuments()->where('document_id', $item)->exists()) {
                            $dolly->digitalDocuments()->attach($item);
                        }
                    }
                    break;

                case 'artifact':
                    foreach($request->items as $item) {
                        $item = (int)$item;
                        if (!$dolly->artifacts()->where('artifact_id', $item)->exists()) {
                            $dolly->artifacts()->attach($item);
                        }
                    }
                    break;

                default:
                    return response()->json(['message' => 'Type non valide'], 400);
            }
        }
            return response()->json(['message' => 'Éléments ajoutés avec succès'], 200);

    }



    public function removeItems(Request $request){
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf,digital_folder,digital_document,artifact',
            'items' => 'required|array',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{
            if($dolly->owner_organisation_id != Auth::user()->current_organisation_id){
                return response()->json(['message' => 'Unauthorized access to this dolly'], 403);
            }
            switch(true){
                case $request->category == 'mail':
                    $dolly->mails()->detach($request->items);
                    break;
                case $request->category == 'communication':
                    $dolly->communications()->detach($request->items);
                    break;
                case $request->category == 'building':
                    $dolly->buildings()->detach($request->items);
                    break;
                case $request->category == 'room':
                    $dolly->rooms()->detach($request->items);
                    break;
                case $request->category == 'record':
                    $dolly->records()->detach($request->items);
                    break;
                case $request->category == 'slip':
                    $dolly->slips()->detach($request->items);
                    break;
                case $request->category == 'shelf':
                    $dolly->shelves()->detach($request->items);
                    break;
                case $request->category == 'container':
                    $dolly->containers()->detach($request->items);
                    break;
                case $request->category == 'digital_folder':
                    $dolly->digitalFolders()->detach($request->items);
                    break;
                case $request->category == 'digital_document':
                    $dolly->digitalDocuments()->detach($request->items);
                    break;
                case $request->category == 'artifact':
                    $dolly->artifacts()->detach($request->items);
                    break;
                default :
                    return response()->json(['message' => 'Dolly not found'], 404);
            }
        }
        return response()->json(['message' => 'Items removed successfully'], 200);

    }



    public function clean(Request $request){
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf,digital_folder,digital_document,artifact',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{
            if($dolly->owner_organisation_id != Auth::user()->current_organisation_id){
                return response()->json(['message' => 'Unauthorized access to this dolly'], 403);
            }
            switch(true){
                case $request->category == 'mail':
                    $dolly->mails()->detach();
                    break;
                case $request->category == 'communication':
                    $dolly->communications()->detach();
                    break;
                case $request->category == 'building':
                    $dolly->buildings()->detach();
                    break;
                case $request->category == 'room':
                    $dolly->rooms()->detach();
                    break;
                case $request->category == 'record':
                    $dolly->records()->detach();
                    break;
                case $request->category == 'slip':
                    $dolly->slips()->detach();
                    break;
                case $request->category == 'shelf':
                    $dolly->shelves()->detach();
                    break;
                case $request->category == 'container':
                    $dolly->containers()->detach();
                    break;
                case $request->category == 'digital_folder':
                    $dolly->digitalFolders()->detach();
                    break;
                case $request->category == 'digital_document':
                    $dolly->digitalDocuments()->detach();
                    break;
                case $request->category == 'artifact':
                    $dolly->artifacts()->detach();
                    break;
                default :
                    return response()->json(['message' => 'Dolly not found'], 404);
            }
        }
        return response()->json(['message' => 'Items removed successfully'], 200);
    }


    public function deleteDolly(Request $request){
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        } else {
            if($dolly->owner_organisation_id != Auth::user()->current_organisation_id){
                return response()->json(['message' => 'Unauthorized access to this dolly'], 403);
            }
            $dolly->mails()->detach();
            $dolly->communications()->detach();
            $dolly->buildings()->detach();
            $dolly->rooms()->detach();
            $dolly->records()->detach();
            $dolly->slips()->detach();
            $dolly->shelves()->detach();
            $dolly->containers()->detach();
            $dolly->digitalFolders()->detach();
            $dolly->digitalDocuments()->detach();
            $dolly->artifacts()->detach();
            $dolly->delete();
        }
        return response()->json(['message' => 'Dolly and its relations deleted successfully'], 200);
    }


}
