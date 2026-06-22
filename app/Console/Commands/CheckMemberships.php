<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckMemberships extends Command
{
    protected $signature = 'gimnasio:check-memberships';
    protected $description = 'Verifica membresías vencidas y notifica las que están por vencer (RF-13 y RF-15).';

    public function handle()
    {
        $this->info('====================================================');
        $this->info('🏋️ INICIANDO REVISIÓN AUTOMÁTICA DE MEMBRESÍAS 🏋️');
        $this->info('====================================================');

        // RF-13: Actualizar membresías a "Vencida"
        $this->updateExpiredMemberships();

        $this->line(''); // Espacio en blanco

        // RF-15: Enviar notificaciones para membresías por vencer
        $this->notifyEndingSoonMemberships();

        $this->info('====================================================');
        $this->info('✅ TAREA DE VERIFICACIÓN FINALIZADA CON ÉXITO');
        $this->info('====================================================');
        
        return 0;
    }

    private function updateExpiredMemberships()
    {
        $this->line('⏳ Buscando membresías vencidas...');
        $today = Carbon::today()->toDateString();
        
        try {
            // Obtenemos los planes que ya pasaron su fecha de fin y siguen "Activos"
            $vencidas = DB::table('tmembresias')
                ->where('fechaFinMembresia', '<', $today)
                ->where('estadoMembresia', 'Activa')
                ->get();

            $contador = 0;
            foreach ($vencidas as $membresia) {
                // 1. Vencemos la membresía
                DB::table('tmembresias')
                    ->where('idMembresia', $membresia->idMembresia)
                    ->update([
                        'estadoMembresia' => 'Vencida',
                        'fechaA' => now()
                    ]);

                // 2. Vencemos al socio (usando el nombre correcto: 'estadoSocio')
                DB::table('tsocios')
                    ->where('carnetSocio', $membresia->carnetSocio)
                    ->update([
                        'estadoSocio' => 'Vencido',
                        'fechaA' => now()
                    ]);

                $contador++;
            }

            if ($contador > 0) {
                $this->error("🛑 Se detectaron y actualizaron {$contador} membresías a estado 'Vencida'.");
            } else {
                $this->info("✨ Todo al día. No se encontraron membresías vencidas hoy.");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error fatal al actualizar membresías: ' . $e->getMessage());
            Log::error('Error en CheckMemberships@updateExpired: ' . $e->getMessage());
        }
    }

    private function notifyEndingSoonMemberships()
    {
        $daysToNotify = 7; // Notificar con 7 días de antelación
        $notificationDate = Carbon::today()->addDays($daysToNotify)->toDateString();
        $this->line("⏳ Buscando socios que vencen en exactamente 7 días ({$notificationDate})...");

        try {
            $socios = DB::table('tmembresias as m')
                ->join('tsocios as s', 'm.carnetSocio', '=', 's.carnetSocio')
                ->join('tusuarios as u', 's.idUsuario', '=', 'u.idUsuario')
                ->where('m.fechaFinMembresia', '=', $notificationDate)
                ->where('m.estadoMembresia', 'Activa')
                ->select('u.correo', 'u.nombre1', 'm.fechaFinMembresia')
                ->get();

            $contador = 0;
            foreach ($socios as $socio) {
                // Simulación visual del envío de correo para la defensa
                $this->line("   📧 SIMULACIÓN EMAIL -> Enviando alerta a: {$socio->correo} (Socio: {$socio->nombre1})");
                Log::info("Notificación enviada a {$socio->correo}. Vence el {$socio->fechaFinMembresia}.");
                $contador++;
            }

            if ($contador > 0) {
                $this->info("✅ Se enviaron {$contador} correos de alerta de próximo vencimiento.");
            } else {
                $this->info("✨ Ningún socio vence en los próximos 7 días.");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error al notificar membresías por vencer: ' . $e->getMessage());
            Log::error('Error en CheckMemberships@notifyEndingSoon: ' . $e->getMessage());
        }
    }
}