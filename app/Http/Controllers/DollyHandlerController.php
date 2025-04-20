<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Dolly;





class DollyHandlerController extends Controller
{
    public function list(Request $request)  : JsonResponse
    {

        $request->validate([
            'type' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf',
        ]);

        $dollies = Dolly::WhereHas('type', function($query) use ($request) {
                $query->where('name', $request->type);
            })
            ->get();

        if(count($dollies) == 0){
            return response()->json(['message' => 'No dollies found'], 404);
        }

        if($request->type == 'shelve'){
            $dollies->load('shelve');
        }else{
            $relation = $request->type . 's';
            $dollies->load($relation);
        }

        return response()->json(['dollies' => $dollies], 200);
    }

    public function addItems(Request $request) : JsonResponse
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'type' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf',
            'items' => 'required|array',
        ]);


        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{

            switch($request->type) {


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

                default:
                    return response()->json(['message' => 'Type non valide'], 400);
            }
        }
            return response()->json(['message' => 'Éléments ajoutés avec succès'], 200);
            
    }



    public function removeItems(Request $request){
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'type' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf',
            'items' => 'required|array',
            'items.*' => 'required|integer|exists:mail,id,communication,id,building,id,room,id,record,id,slip,id,container,id,shelf,id',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{
            switch(true){
                case $request->type == 'mail':
                    $dolly->mails()->detach($request->items);
                    break;
                case $request->type == 'communication':
                    $dolly->communications()->detach($request->items);
                    break;
                case $request->type == 'building':
                    $dolly->buildings()->detach($request->items);
                    break;
                case $request->type == 'room':
                    $dolly->rooms()->detach($request->items);
                    break;
                case $request->type == 'record':
                    $dolly->records()->detach($request->items);
                    break;
                case $request->type == 'slip':
                    $dolly->slips()->detach($request->items);
                    break;
                case $request->type == 'shelf':
                    $dolly->shelves()->detach($request->items);
                    break;
                case $request->type == 'container':
                    $dolly->containers()->detach($request->items);
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
            'type' => 'required|string|in:mail,communication, building, transferring, building, room, record, slip, slipRecord, container, shelf',
        ]);

        $dolly = Dolly::find($request->dolly_id);
        if(!$dolly){
            return response()->json(['message' => 'Dolly not found'], 404);
        }else{
            switch(true){
                case $request->type == 'mail':
                    $dolly->mails()->detach();
                    break;
                case $request->type == 'communication':
                    $dolly->communications()->detach();
                    break;
                case $request->type == 'building':
                    $dolly->buildings()->detach();
                    break;
                case $request->type == 'room':
                    $dolly->rooms()->detach();
                    break;
                case $request->type == 'record':
                    $dolly->records()->detach();
                    break;
                case $request->type == 'slip':
                    $dolly->slips()->detach();
                    break;
                case $request->type == 'shelf':
                    $dolly->shelves()->detach();
                    break;
                case $request->type == 'container':
                    $dolly->containers()->detach();
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
            // Detach all related items
            $dolly->mails()->detach();
            $dolly->communications()->detach();
            $dolly->buildings()->detach();
            $dolly->rooms()->detach();
            $dolly->records()->detach();
            $dolly->slips()->detach();
            $dolly->shelves()->detach();
            $dolly->containers()->detach();
            $dolly->delete();
        }
        return response()->json(['message' => 'Dolly and its relations deleted successfully'], 200);
    }


}
