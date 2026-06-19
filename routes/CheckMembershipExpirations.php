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
        $this->info('Iniciando la verificacion de membresias (RF-13 y RF-15)...');
        $today = now()->format('Y-m-d');

        $todas = DB::select('CALL sp_TMembresias_Select()');

        foreach ($todas as $m) {
            if ($m->estadoMembresia === 'Activa' && $m->fechaFinMembresia < $today) {
                DB::select('CALL sp_TMembresias_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                    $m->idMembresia,
                    $m->idPlan,
                    $m->carnetSocio,
                    $m->idSucursal,
                    $m->fechaInicioMembresia,
                    $m->fechaFinMembresia,
                    'Vencida',
                    1,
                    '127.0.0.1',
                ]);
                $this->info("Membresia {$m->idMembresia} marcada como Vencida.");
            }
        }

        $targetDate = now()->addDays(7)->format('Y-m-d');

        foreach ($todas as $m) {
            if ($m->estadoMembresia === 'Activa' && $m->fechaFinMembresia === $targetDate) {
                $socios = DB::select('CALL sp_TSocios_SelectById(?)', [$m->carnetSocio]);
                if (!empty($socios)) {
                    $usuarios = DB::select('CALL sp_TUsuarios_SelectById(?)', [$socios[0]->idUsuario]);
                    if (!empty($usuarios)) {
                        Log::info("EMAIL AUTOMATICO (RF-15) -> Para: {$usuarios[0]->correo} | Asunto: Tu membresia vence pronto | Mensaje: Hola {$usuarios[0]->nombre1}, te recordamos que tu plan vence el {$m->fechaFinMembresia}.");
                        $this->info("Correo de advertencia preparado para: {$usuarios[0]->correo}");
                    }
                }
            }
        }

        $this->info('Proceso completado con exito.');
    }
}