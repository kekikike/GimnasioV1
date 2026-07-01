<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EsquemaSueldoController extends Controller
{
    private function getEmpleadosSinEsquemaQuery()
    {
        $usuario = session('usuario');
        $query = DB::table('templeados as e')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->leftJoin('tesquemasueldos as es', function ($join) {
                $join->on('e.carnetEmpleado', '=', 'es.carnetEmpleado')
                     ->where('es.estadoA', 1);
            })
            ->where('e.estadoA', 1)
            ->where('u.idUsuario', '!=', 1)
            ->whereNull('es.carnetEmpleado')
            ->select('e.carnetEmpleado', 'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2', 'u.idRol', 'e.idSucursal');
        $emp = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->first();
        if ($emp) $query->where('e.idSucursal', $emp->idSucursal);
        return $query;
    }

    private function getEsquemasQuery()
    {
        $usuario = session('usuario');
        $query = DB::table('tesquemasueldos as es')
            ->join('templeados as e', 'es.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('es.estadoA', 1)
            ->where('u.idUsuario', '!=', 1)
            ->select('es.*', 'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2', 'u.idRol', 'e.idSucursal');
        $emp = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->first();
        if ($emp) $query->where('e.idSucursal', $emp->idSucursal);
        return $query->orderBy('e.carnetEmpleado');
    }

    public function index()
    {
        $modalidades = ['Fijo Mensual', 'Por Hora', 'Por Actividad'];
        return view('admin.esquema-sueldos', compact('modalidades'));
    }

    public function listar()
    {
        $esquemas = $this->getEsquemasQuery()->get();
        $empleados = $this->getEmpleadosSinEsquemaQuery()->get();
        return response()->json(compact('esquemas', 'empleados'));
    }

    private function validarTarifaSegunRol($carnetEmpleado, $tarifa)
    {
        $empleado = DB::table('templeados as e')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('e.carnetEmpleado', $carnetEmpleado)
            ->select('u.idRol')
            ->first();
        if (!$empleado) return 'El empleado no existe.';
        if ($empleado->idRol != 3 && $tarifa > 0) {
            return 'Solo los entrenadores pueden tener tarifa por hora/clase.';
        }
        return null;
    }

    public function store(Request $request)
    {
        $usuario = session('usuario');

        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required|numeric|exists:templeados,carnetEmpleado',
            'modalidadPago' => 'required|string|max:50',
            'montoBase' => 'required|numeric|min:0|max:999999.99',
            'tarifaHoraOClase' => 'required|integer|min:0|max:999999',
        ], [
            'carnetEmpleado.required' => 'Seleccione un empleado.',
            'carnetEmpleado.exists' => 'El empleado no existe.',
            'modalidadPago.required' => 'La modalidad de pago es requerida.',
            'montoBase.required' => 'El monto base es requerido.',
            'montoBase.numeric' => 'El monto base debe ser un numero valido.',
            'montoBase.min' => 'El monto base no puede ser negativo.',
            'montoBase.max' => 'El monto base no debe superar los 999,999.99 Bs.',
            'tarifaHoraOClase.required' => 'La tarifa es requerida.',
            'tarifaHoraOClase.integer' => 'La tarifa debe ser un numero entero.',
            'tarifaHoraOClase.min' => 'La tarifa no puede ser negativa.',
            'tarifaHoraOClase.max' => 'La tarifa no debe superar los 999,999.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $error = $this->validarTarifaSegunRol($request->carnetEmpleado, $request->tarifaHoraOClase);
        if ($error) {
            return response()->json(['success' => false, 'errors' => ['tarifaHoraOClase' => [$error]]], 422);
        }

        $this->authorizeSucursal($usuario, $request->carnetEmpleado);

        $nuevoId = DB::table('tesquemasueldos')->insertGetId([
            'carnetEmpleado' => $request->carnetEmpleado,
            'modalidadPago' => $request->modalidadPago,
            'montoBase' => $request->montoBase,
            'tarifaHoraOClase' => $request->tarifaHoraOClase,
            'usuarioA' => session('usuario')->idUsuario ?? 1,
            'fechaA' => now(),
            'estadoA' => 1,
        ]);

        DB::table('tauditorias')->insert([
            'tablaNombre'   => 'tesquemasueldos',
            'registroId'    => $nuevoId,
            'accion'        => 'I',
            'campo'         => 'carnetEmpleado|modalidadPago|montoBase|tarifaHoraOClase',
            'valorAnterior' => null,
            'valorNuevo'    => "{$request->carnetEmpleado}|{$request->modalidadPago}|{$request->montoBase}|{$request->tarifaHoraOClase}",
            'usuarioA'      => session('usuario')->idUsuario ?? 1,
            'fechaA'        => now(),
            'direccionIP'   => $request->ip(),
            'detalles'      => 'Creacion de esquema de sueldo',
        ]);

        return response()->json(['success' => true, 'message' => 'Esquema de sueldo registrado.']);
    }

    public function update(Request $request, $id)
    {
        $esquema = DB::table('tesquemasueldos')->where('idEsquemaSueldo', $id)->where('estadoA', 1)->first();
        if (!$esquema) {
            return response()->json(['success' => false, 'message' => 'El esquema no existe.'], 404);
        }

        $usuario = session('usuario');
        $this->authorizeSucursal($usuario, $esquema->carnetEmpleado);

        $validator = Validator::make($request->all(), [
            'modalidadPago' => 'required|string|max:50',
            'montoBase' => 'required|numeric|min:0|max:999999.99',
            'tarifaHoraOClase' => 'required|integer|min:0|max:999999',
        ], [
            'modalidadPago.required' => 'La modalidad de pago es requerida.',
            'montoBase.required' => 'El monto base es requerido.',
            'montoBase.numeric' => 'El monto base debe ser un numero valido.',
            'montoBase.min' => 'El monto base no puede ser negativo.',
            'montoBase.max' => 'El monto base no debe superar los 999,999.99 Bs.',
            'tarifaHoraOClase.required' => 'La tarifa es requerida.',
            'tarifaHoraOClase.integer' => 'La tarifa debe ser un numero entero.',
            'tarifaHoraOClase.min' => 'La tarifa no puede ser negativa.',
            'tarifaHoraOClase.max' => 'La tarifa no debe superar los 999,999.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $error = $this->validarTarifaSegunRol($esquema->carnetEmpleado, $request->tarifaHoraOClase);
        if ($error) {
            return response()->json(['success' => false, 'errors' => ['tarifaHoraOClase' => [$error]]], 422);
        }

        $old = DB::table('tesquemasueldos')->where('idEsquemaSueldo', $id)->first();
        DB::table('tesquemasueldos')->where('idEsquemaSueldo', $id)->update([
            'modalidadPago' => $request->modalidadPago,
            'montoBase' => $request->montoBase,
            'tarifaHoraOClase' => $request->tarifaHoraOClase,
            'usuarioA' => session('usuario')->idUsuario ?? 1,
            'fechaA' => now(),
        ]);

        DB::table('tauditorias')->insert([
            'tablaNombre'   => 'tesquemasueldos',
            'registroId'    => $id,
            'accion'        => 'U',
            'campo'         => 'modalidadPago|montoBase|tarifaHoraOClase',
            'valorAnterior' => "{$old->modalidadPago}|{$old->montoBase}|{$old->tarifaHoraOClase}",
            'valorNuevo'    => "{$request->modalidadPago}|{$request->montoBase}|{$request->tarifaHoraOClase}",
            'usuarioA'      => session('usuario')->idUsuario ?? 1,
            'fechaA'        => now(),
            'direccionIP'   => $request->ip(),
            'detalles'      => 'Actualizacion de esquema de sueldo',
        ]);

        return response()->json(['success' => true, 'message' => 'Esquema de sueldo actualizado.']);
    }

    public function destroy($id)
    {
        $esquema = DB::table('tesquemasueldos')->where('idEsquemaSueldo', $id)->where('estadoA', 1)->first();
        if (!$esquema) {
            return response()->json(['success' => false, 'message' => 'El esquema no existe.'], 404);
        }

        $usuario = session('usuario');
        $this->authorizeSucursal($usuario, $esquema->carnetEmpleado);

        DB::table('tesquemasueldos')->where('idEsquemaSueldo', $id)->update([
            'estadoA' => 0,
            'usuarioA' => session('usuario')->idUsuario ?? 1,
            'fechaA' => now(),
        ]);

        DB::table('tauditorias')->insert([
            'tablaNombre'   => 'tesquemasueldos',
            'registroId'    => $id,
            'accion'        => 'D',
            'campo'         => 'estadoA',
            'valorAnterior' => '1',
            'valorNuevo'    => '0',
            'usuarioA'      => session('usuario')->idUsuario ?? 1,
            'fechaA'        => now(),
            'direccionIP'   => request()->ip(),
            'detalles'      => 'Baja de esquema de sueldo',
        ]);

        return response()->json(['success' => true, 'message' => 'Esquema de sueldo eliminado.']);
    }

    private function authorizeSucursal($usuario, $carnetEmpleado)
    {
        $empleado = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->first();
        $target = DB::table('templeados')->where('carnetEmpleado', $carnetEmpleado)->first();
        if (!$empleado || !$target || $empleado->idSucursal != $target->idSucursal) {
            abort(403, 'No tienes permiso para gestionar esquemas de este empleado.');
        }
    }
}
