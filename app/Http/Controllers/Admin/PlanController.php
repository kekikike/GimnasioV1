<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index() {
        return view('admin.planes');
    }

    public function listar() {
        try {
            $planes = DB::table('tplanes')->where('estadoA', 1)->get();
            return response()->json($planes);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        // RF12: Validación para que el nombre contenga letras y descripción mínima
        $validator = Validator::make($request->all(), [
            'nombrePlan'   => ['required', 'string', 'regex:/[a-zA-Z]/', 'max:100'],
            'descripcion'  => 'required|string|min:15|max:255',
            'costoPlan'    => 'required|numeric|min:0',
            'duracionDias' => 'required|integer|min:1|max:366'
        ], [
            'nombrePlan.regex' => 'El nombre del plan debe contener al menos una letra.',
            'nombrePlan.max' => 'El nombre no debe exceder 100 caracteres.',
            'descripcion.min' => 'La descripcion debe tener al menos 15 caracteres.',
            'duracionDias.max' => 'La duracion no debe exceder 366 dias.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        try {
            $idPlan = DB::table('tplanes')->insertGetId([
                'nombrePlan'   => $request->nombrePlan,
                'descripcion'  => $request->descripcion,
                'costoPlan'    => $request->costoPlan,
                'duracionDias' => $request->duracionDias,
                'estadoA'      => 1,
                'fechaA'       => now(),
                'usuarioA'     => $usuarioA
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tplanes',
                'registroId'    => $idPlan,
                'accion'        => 'I',
                'campo'         => 'nombrePlan|descripcion|costoPlan|duracionDias',
                'valorAnterior' => '|||',
                'valorNuevo'    => implode('|', [$request->nombrePlan, $request->descripcion, $request->costoPlan, $request->duracionDias]),
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Insercion de Plan'
            ]);

            return response()->json(['success' => true, 'message' => 'Plan de membresia creado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'nombrePlan'   => ['required', 'string', 'regex:/[a-zA-Z]/', 'max:100'],
            'descripcion'  => 'required|string|min:15|max:255',
            'costoPlan'    => 'required|numeric|min:0',
            'duracionDias' => 'required|integer|min:1|max:366'
        ], [
            'nombrePlan.required' => 'El nombre del plan es obligatorio.',
            'nombrePlan.regex' => 'El nombre del plan debe contener letras (no puede ser solo números).',
            'nombrePlan.max' => 'El nombre del plan no debe exceder 100 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.min'  => 'La descripción es muy corta. Escribe al menos 15 caracteres detallando el plan.',
            'descripcion.max' => 'La descripción no debe exceder 255 caracteres.',
            'costoPlan.required' => 'El costo del plan es obligatorio.',
            'costoPlan.numeric' => 'El costo debe ser un número.',
            'costoPlan.min' => 'El costo no puede ser negativo.',
            'duracionDias.required' => 'La duración es obligatoria.',
            'duracionDias.integer' => 'La duración debe ser un número entero.',
            'duracionDias.min' => 'La duración debe ser al menos 1 día.',
            'duracionDias.max' => 'La duración no puede exceder 366 días.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;

        $viejo = DB::table('tplanes')->where('idPlan', $id)->first();

        try {
            DB::table('tplanes')->where('idPlan', $id)->update([
                'nombrePlan'   => $request->nombrePlan,
                'descripcion'  => $request->descripcion,
                'costoPlan'    => $request->costoPlan,
                'duracionDias' => $request->duracionDias,
                'fechaA'       => now(),
                'usuarioA'     => $usuarioA
            ]);

            $campos = []; $viejos = []; $nuevos = [];
            foreach (['nombrePlan', 'descripcion', 'costoPlan', 'duracionDias'] as $c) {
                $oldVal = $viejo->$c ?? '';
                $newVal = $request->$c ?? '';
                if ((string)$oldVal !== (string)$newVal) {
                    $campos[] = $c; $viejos[] = $oldVal; $nuevos[] = $newVal;
                }
            }
            if (!empty($campos)) {
                DB::table('tauditorias')->insert([
                    'tablaNombre'   => 'tplanes',
                    'registroId'    => $id,
                    'accion'        => 'U',
                    'campo'         => implode('|', $campos),
                    'valorAnterior' => implode('|', $viejos),
                    'valorNuevo'    => implode('|', $nuevos),
                    'usuarioA'      => $usuarioA,
                    'fechaA'        => now(),
                    'direccionIP'   => $request->ip(),
                    'detalles'      => 'Actualizacion de Plan'
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Plan actualizado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id) {
        $usuarioA = Auth::id() ?? 1;
        try {
            // Verificar si hay membresías activas con este plan
            $membresiasActivas = DB::table('TMembresias')
                ->where('idPlan', $id)
                ->where('estadoA', 1)
                ->count();

            if ($membresiasActivas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede dar de baja el plan porque tiene {$membresiasActivas} membresía(s) activa(s) asignada(s)."
                ], 422);
            }

            DB::table('tplanes')->where('idPlan', $id)->update(['estadoA' => 0, 'fechaA' => now(), 'usuarioA' => $usuarioA]);
            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tplanes',
                'registroId'    => $id,
                'accion'        => 'D',
                'campo'         => 'estadoA',
                'valorAnterior' => '1',
                'valorNuevo'    => '0',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Baja de Plan'
            ]);
            return response()->json(['success' => true, 'message' => 'Plan dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()], 500);
        }
    }
}