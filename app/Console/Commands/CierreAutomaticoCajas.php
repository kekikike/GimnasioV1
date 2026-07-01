<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CierreAutomaticoCajas extends Command
{
    protected $signature = 'gimnasio:cierre-cajas';
    protected $description = 'Cierra automaticamente las cajas abiertas a las 23:59, asignando montoCierre = montoCierreCalculado';

    public function handle()
    {
        $this->info('====================================================');
        $this->info('INICIANDO CIERRE AUTOMATICO DE CAJAS');
        $this->info('====================================================');

        try {
            $cajasAbiertas = DB::table('TCajas')
                ->where('estadoCaja', 'Abierta')
                ->where('estadoA', 1)
                ->get();

            if ($cajasAbiertas->isEmpty()) {
                $this->info('No hay cajas abiertas para cerrar.');
                $this->info('====================================================');
                return 0;
            }

            $this->line("Se encontraron {$cajasAbiertas->count()} caja(s) abierta(s).");
            $hoy = now()->format('Y-m-d');

            foreach ($cajasAbiertas as $caja) {
                $this->line("Procesando caja ID {$caja->idCaja} (Sucursal {$caja->idSucursal})...");

                $totalRecibos = DB::table('TRecibos')
                    ->where('idCaja', $caja->idCaja)
                    ->whereDate('fechaPago', $hoy)
                    ->where('estadoA', 1)
                    ->sum('montoTotal') ?? 0;

                $totalSalidas = DB::table('TSalidas')
                    ->where('idCaja', $caja->idCaja)
                    ->whereDate('fechaA', $hoy)
                    ->where('estadoA', 1)
                    ->sum('costo') ?? 0;

                $montoCierreCalculado = $caja->montoApertura + $totalRecibos - $totalSalidas;

                DB::table('TCajas')
                    ->where('idCaja', $caja->idCaja)
                    ->update([
                        'montoCierre' => $montoCierreCalculado,
                        'montoCierreCalculado' => $montoCierreCalculado,
                        'diferenciaArqueo' => 0,
                        'cierreEstado' => 'Bien',
                        'cierreObservacion' => null,
                        'estadoCaja' => 'Cerrada',
                        'fechaA' => now(),
                        'usuarioA' => 1,
                    ]);

                DB::table('TAuditorias')->insert([
                    'tablaNombre' => 'TCajas',
                    'registroId' => $caja->idCaja,
                    'accion' => 'UPDATE',
                    'campo' => 'estadoCaja',
                    'valorAnterior' => 'Abierta',
                    'valorNuevo' => 'Cerrada',
                    'usuarioA' => 1,
                    'fechaA' => now(),
                    'direccionIP' => '127.0.0.1',
                    'detalles' => "Cierre automatico por scheduler. Monto calculado: {$montoCierreCalculado}, Diferencia: 0",
                ]);

                $this->line("   Caja ID {$caja->idCaja} cerrada automaticamente. Monto cierre: {$montoCierreCalculado}");
            }

            $this->info("CIERRE AUTOMATICO COMPLETADO: {$cajasAbiertas->count()} caja(s) cerrada(s).");
        } catch (\Exception $e) {
            $this->error('Error en cierre automatico de cajas: ' . $e->getMessage());
            Log::error('Error en CierreAutomaticoCajas: ' . $e->getMessage());
            return 1;
        }

        $this->info('====================================================');
        return 0;
    }
}
