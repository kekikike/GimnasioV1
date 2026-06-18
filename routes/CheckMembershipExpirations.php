<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckMembershipExpirations extends Command
{
    protected $signature = 'memberships:check-expirations';
    protected $description = 'Verifica membresías vencidas y por vencer, actualiza su estado y envía notificaciones.';

    public function handle()
    {
        $this->info('Iniciando la verificación de membresías (RF-13 y RF-15)...');
        $today = \Carbon\Carbon::today()->format('Y-m-d');

        // RF-13: Controlar automáticamente el vencimiento
        $expiredMemberships = DB::table('tmembresias')
            ->where('fechaFin', '<', $today)
            ->where('estadoMembresia', 'Activa')
            ->get();

        foreach ($expiredMemberships as $membership) {
            DB::table('tmembresias')->where('idMembresia', $membership->idMembresia)->update(['estadoMembresia' => 'Vencida']);
            $this->info("🔴 Membresía {$membership->idMembresia} marcada como Vencida.");
        }

        // RF-15: Notificaciones de vencimiento (Aviso con 7 días de anticipación)
        $targetDate = \Carbon\Carbon::today()->addDays(7)->format('Y-m-d');

        $expiringMemberships = DB::table('tmembresias')
            ->join('tsocios', 'tmembresias.carnetSocio', '=', 'tsocios.carnetSocio')
            ->join('tusuarios', 'tsocios.idUsuario', '=', 'tusuarios.idUsuario')
            ->whereDate('tmembresias.fechaFin', $targetDate)
            ->where('tmembresias.estadoMembresia', 'Activa')
            ->select('tusuarios.correo', 'tusuarios.nombre1', 'tmembresias.fechaFin')
            ->get();

        foreach ($expiringMemberships as $membership) {
            // Simulamos el envío de correo guardándolo en el registro del sistema para la defensa
            Log::info("🔔 EMAIL AUTOMÁTICO (RF-15) -> Para: {$membership->correo} | Asunto: Tu membresía vence pronto | Mensaje: Hola {$membership->nombre1}, te recordamos que tu plan vence el {$membership->fechaFin}. ¡No pierdas tu progreso!");
            $this->info("📧 Correo de advertencia preparado para: {$membership->correo}");
        }

        $this->info('✅ Proceso completado con éxito.');
    }
}