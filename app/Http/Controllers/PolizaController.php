<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
class PolizaController extends Controller
{
    public function guardarPoliza(Request $request){

       // try {
            $id_persona = '';
            $id_persona = '';
            $json = json_encode($request->input());
            $ojso = json_decode($json, true);
            $polizaId = $request->get('id_poliza');
            $personaId = 0;
            $miCliente = $ojso["cliente"];
            $id_cliente = $miCliente["id_cliente"];
            $id_vigente = 1;

            if($id_cliente === 0){
                DB::insert('insert into persona (nombre, fecha_nacimiento, email, telefono) 
                values 
                (?,?,?,?)', 
                [$miCliente["nombre"], $miCliente["fecha_nacimiento"], $miCliente["email"], 
                $miCliente["telefono"]]);
                $id_persona = DB::getPdo()->lastInsertId();
                DB::insert('insert into cliente (persona_id, agente_id) 
                values (?, ?)', 
                [$id_persona, $request["id_agente"]]);   
                $id_cliente = DB::getPdo()->lastInsertId();
            }else{
                DB::update('update persona set nombre = ?, fecha_nacimiento = ?,
                email = ?, telefono = ?
                where id = ?', 
                [$miCliente["nombre"], $miCliente["fecha_nacimiento"], $miCliente["email"], 
                $miCliente["telefono"], $miCliente["id_persona"]]);
            }
            if($polizaId === 0){
                // Insertar Poliza
                DB::insert('insert into poliza 
                (aseguradora, precio, fecha_inicio, fecha_vigencia, cliente_id, 
                    tipo_id, estatus_id, agente_id) 
                values (?,?,?,?,?,?,?,?)', 
                [$request["aseguradora"], $request["precio"], $request["fecha_inicio"], 
                $request["fecha_vigencia"], $id_cliente, $request["id_tipo"], $id_vigente, $request["id_agente"]]);
                $polizaId = DB::getPdo()->lastInsertId();
            }else{
                DB::update('update poliza 
                set aseguradora = ?, precio = ?, fecha_inicio = ?, fecha_vigencia = ?, cliente_id = ?, 
                tipo_id = ?, estatus_id = ? where id = ?', 
                [$request["aseguradora"], $request["precio"], $request["fecha_inicio"], 
                $request["fecha_vigencia"], $id_cliente, $request["tipo_id"], $request["id_estatus"], $polizaId]);
            }
            if($request->get("asegurado")){
                $personas = $ojso["asegurado"];
                foreach($personas as $persona){
                    $personaId = $persona['id_persona'];
                    if( $personaId == 0){
                       // Insertar persona
                       DB::insert('insert into persona (nombre, fecha_nacimiento, email, telefono) 
                        values 
                        (?,?,?,?)', 
                        [$persona["nombre"], $persona["fecha_nacimiento"], $persona["email"], 
                        $persona["telefono"]]);
                        $personaId = DB::getPdo()->lastInsertId();
                        DB::insert('insert into asegurado 
                        (persona_id, poliza_id) 
                        values (?, ?)',
                        [$personaId, $polizaId]);                        
                    }else{
                        // actualizar persona
                        DB::update('update persona set nombre = ?, fecha_nacimiento = ?,
                        email = ?, telefono = ?
                        where id = ?', 
                        [$persona["nombre"], $persona["fecha_nacimiento"], $persona["email"], 
                        $persona["telefono"], $persona["id_persona"]]);
                    }                      
               }
            }   
            return response()->json(['data'=>"Se ha registrado correctamente", 'ok'=>true], 200);
        // } catch (\Throwable $th) {
        //     return response()->json(['data'=>"Ha ocurrido un error ".$th, 'ok'=>false], 300);
        // }        
    }
    public function getPolizasPorAgente($id, $index){
        try {
            $contador = 0;
            $data = DB::table('poliza as po')
            ->join('cliente as cl', 'cl.id', '=', 'po.cliente_id')
            ->join('persona as pc', 'pc.id', '=', 'cl.persona_id')
            ->join('estatus as e', 'e.id', '=', 'po.estatus_id')
            ->join('tipo as ti', 'ti.id', '=', 'po.tipo_id')
            ->select('po.id as id_poliza', 'po.aseguradora', 'po.precio',
                        'po.fecha_inicio', 'po.fecha_vigencia', 'po.estatus_id as id_status',
                        'po.agente_id as id_agente', 'e.estatus', 'pc.nombre', 'pc.email', 
                        'pc.telefono', 'pc.fecha_nacimiento', 'ti.tipo')                        
            ->where('po.agente_id', $id)
            ->take(6)
            ->skip($index)
            ->get();
            foreach($data as $miData){
                $asegurados = DB::table('asegurado as a')
                ->join('persona as p', 'p.id', '=', 'a.persona_id')
                ->select('p.nombre', 'p.email', 'p.telefono', 'p.fecha_nacimiento')
                ->where('a.poliza_id', $miData->id_poliza)
                ->get(); 
                if($contador == 0){
                    $data=json_decode(json_encode($data), true);
                }
                $data[$contador]+=["asegurados"=>$asegurados];
                $contador ++;
            }            
            return response()->json(['data'=>$data, 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error ".$th, 'ok'=>false], 300);
        }
       
    }
    public function eliminarPolizas(Request $request){
        try {
        
            DB::table('polizas')->delete($request["id_poliza"]);

            return response()->json(['data'=>"Se ha eliminado correctamente", 'ok'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['data'=>"Ha ocurrido un error ". $th, 'ok'=>false], 300);
        }       
    }


}            
