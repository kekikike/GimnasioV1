<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckMemberships extends Command
{
    protected $signature = 'gimnasio:check-memberships';
    protected $description = 'Verifica membresias vencidas y notifica las que estan por vencer (RF-13 y RF-15).';

    public function handle()
    {
        $this->info('====================================================');
        $this->info('INICIANDO REVISION AUTOMATICA DE MEMBRESIAS');
        $this->info('====================================================');

        $this->updateExpiredMemberships();

        $this->line('');

        $this->notifyEndingSoonMemberships();

        $this->info('====================================================');
        $this->info('TAREA DE VERIFICACION FINALIZADA CON EXITO');
        $this->info('====================================================');
        
        return 0;
    }

    private function updateExpiredMemberships()
    {
        $this->line('Buscando membresias vencidas...');
        $today = Carbon::today()->toDateString();
        
        try {
            $vencidas = DB::table('tmembresias')
                ->where('fechaFinMembresia', '<', $today)
                ->where('estadoMembresia', 'Activa')
                ->get();

            $contador = 0;
            foreach ($vencidas as $membresia) {
                DB::table('tmembresias')
                    ->where('idMembresia', $membresia->idMembresia)
                    ->update([
                        'estadoMembresia' => 'Vencida',
                        'fechaA' => now()
                    ]);

                DB::table('tsocios')
                    ->where('carnetSocio', $membresia->carnetSocio)
                    ->update([
                        'estadoSocio' => 'Vencido',
                        'fechaA' => now()
                    ]);

                DB::table('tauditorias')->insert([
                    'tablaNombre'   => 'tmembresias',
                    'registroId'    => $membresia->idMembresia,
                    'accion'        => 'U',
                    'campo'         => 'estadoMembresia',
                    'valorAnterior' => 'Activa',
                    'valorNuevo'    => 'Vencida',
                    'usuarioA'      => 1,
                    'fechaA'        => now(),
                    'direccionIP'   => '127.0.0.1',
                    'detalles'      => 'Vencimiento automatico por scheduler'
                ]);

                $contador++;
            }

            if ($contador > 0) {
                $this->error("Se detectaron y actualizaron {$contador} membresias a estado 'Vencida'.");
            } else {
                $this->info("Todo al dia. No se encontraron membresias vencidas hoy.");
            }

        } catch (\Exception $e) {
            $this->error('Error fatal al actualizar membresias: ' . $e->getMessage());
            Log::error('Error en CheckMemberships@updateExpired: ' . $e->getMessage());
        }
    }

    private function notifyEndingSoonMemberships()
    {
        $daysToNotify = 7;
        $notificationDate = Carbon::today()->addDays($daysToNotify)->toDateString();
        $this->line("Buscando socios que vencen en exactamente 7 dias ({$notificationDate})...");

        try {
            $socios = DB::table('tmembresias as m')
                ->join('tsocios as s', 'm.carnetSocio', '=', 's.carnetSocio')
                ->join('tusuarios as u', 's.idUsuario', '=', 'u.idUsuario')
                ->where('m.fechaFinMembresia', '=', $notificationDate)
                ->where('m.estadoMembresia', 'Activa')
                ->select('u.correo', 'u.nombre1', 'u.nombre2', 'u.apellido1', 's.idUsuario', 'm.fechaFinMembresia', 'm.idSucursal')
                ->get();

            $contador = 0;
            foreach ($socios as $socio) {
                $existe = DB::table('tnotificaciones')
                    ->where('idUsuario', $socio->idUsuario)
                    ->where('tipoNotificacion', 'Recordatorio')
                    ->whereDate('fechaEnvio', now()->format('Y-m-d'))
                    ->exists();

                if ($existe) {
                    $this->line("   Ya se notifico a {$socio->nombre1} {$socio->apellido1} hoy. Saltando.");
                    continue;
                }

                $nombreCompleto = trim("{$socio->nombre1} {$socio->nombre2} {$socio->apellido1}");
                $fechaVen = Carbon::parse($socio->fechaFinMembresia)->format('d/m/Y');

                DB::table('tnotificaciones')->insert([
                    'idUsuario' => $socio->idUsuario,
                    'tipoNotificacion' => 'Recordatorio',
                    'mensaje' => "Hola {$nombreCompleto}, su membresia vencera el {$fechaVen}. Renueve ahora para no perder el acceso.",
                    'fechaEnvio' => now()->format('Y-m-d'),
                    'estado' => 'Enviado',
                    'usuarioA' => 1,
                ]);

                $this->line("   Notificacion registrada para: {$socio->correo} (Socio: {$socio->nombre1})");
                Log::info("Notificacion creada para {$socio->correo}. Vence el {$socio->fechaFinMembresia}.");
                $contador++;
            }

            if ($contador > 0) {
                $this->info("Se registraron {$contador} notificaciones de proximo vencimiento.");
            } else {
                $this->info("Ningun socio vence en los proximos 7 dias, o ya fueron notificados.");
            }

        } catch (\Exception $e) {
            $this->error('Error al notificar membresias por vencer: ' . $e->getMessage());
            Log::error('Error en CheckMemberships@notifyEndingSoon: ' . $e->getMessage());
        }
    }
}
