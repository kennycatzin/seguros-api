<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
class ClienteController extends Controller
{
    public function guardarCliente(Request $request, $idAgente){

        try {
            DB::insert('insert into persona (nombre, fecha_nacimiento, email, telefono) 
            values 
            (?,?,?,?)', 
            [$request["nombre"], $request["fecha_nacimiento"], $request["email"], $request["telefono"]]);
            $id_persona = DB::getPdo()->lastInsertId();
            DB::insert('insert into cliente (persona_id, agente_id) 
            values 
            (?, ?)', 
            [$id_persona, $idAgente]);    
            return response()->json(['data'=>"Se ha registrado correctamente", 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error", 'ok'=>false], 300);
        }        
    }
    public function getClientesPorAgente($id, $index){
        try {
            $data = DB::table('cliente as c')
            ->join('persona as p', 'p.id', '=', 'c.persona_id')
            ->select('p.id as id_persona', 'c.id as id_cliente', 'p.nombre', 
            'p.telefono', 'p.email', 'p.fecha_nacimiento')
            ->where('c.agente_id', $id)
            ->take(6)
            ->skip($index)
            ->get();
            return response()->json(['data'=>$data, 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error", 'ok'=>false], 300);
        }
       
    }
    public function actualizarCliente(Request $request){
        try {
            DB::update('update persona set nombre = ?, fecha_nacimiento = ?,
            email = ?, telefono = ?
            where id = ?', 
            [$request["nombre"], $request["fecha_nacimiento"], $request["email"], 
             $request["telefono"], $request["id_persona"]]);
            return response()->json(['data'=>"Se ha actualizado correctamente", 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error", 'ok'=>false], 300);
        }       
    }
    public function eliminarCliente(Request $request){
        try {

        
            DB::table('persona')
            ->where('id', $request["id_persona"])
            ->delete();
            
            DB::table('cliente')
            ->where('id', $request["id_cliente"])
            ->delete();
            return response()->json(['data'=>"Se ha eliminado correctamente", 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error ". $th, 'ok'=>false], 300);
        }       
    }


}            
