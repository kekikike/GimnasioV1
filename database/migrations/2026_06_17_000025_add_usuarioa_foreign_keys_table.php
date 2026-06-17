<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'TRoles'              => 'fk_troles_usuarioA',
        'TUsuarios'            => 'fk_tusuarios_usuarioA',
        'TSucursales'           => 'fk_tsucursales_usuarioA',
        'TEmpleados'           => 'fk_templeados_usuarioA',
        'THorarioLaborales'     => 'fk_thorario_usuarioA',
        'TControlAsistencias'  => 'fk_tasistencias_usuarioA',
        'TEsquemaSueldos'      => 'fk_tesquemasueldos_usuarioA',
        'TSocios'              => 'fk_tsocios_usuarioA',
        'TPlanes'               => 'fk_tplanes_usuarioA',
        'TMembresias'          => 'fk_tmembresias_usuarioA',
        'TControlAccesos'      => 'fk_taccesos_usuarioA',
        'TPenalizaciones'       => 'fk_tpenalizaciones_usuarioA',
        'TNotificaciones'       => 'fk_tnotificaciones_usuarioA',
        'TActividades'          => 'fk_tactividades_usuarioA',
        'TClaseGrupales'        => 'fk_tclasegrupales_usuarioA',
        'TReservas'            => 'fk_treservas_usuarioA',
        'TCajas'               => 'fk_tcajas_usuarioA',
        'TRecibos'             => 'fk_trecibos_usuarioA',
        'TDetalleMetodoPagos'  => 'fk_tdetallepago_usuarioA',
        'TMarcas'              => 'fk_tmarcas_usuarioA',
        'TEquipamientos'       => 'fk_tequipamientos_usuarioA',
        'TMantenimientoPreventivos' => 'fk_tmantenimiento_usuarioA',
        'TReporteFallas'       => 'fk_treportefallas_usuarioA',
        'TAuditorias'         => 'fk_tauditoria_usuarioA',
    ];

    public function up(): void
    {
        Schema::table('TUsuarios', function (Blueprint $table) {
            $table->unsignedInteger('usuarioA')->nullable()->change();
        });

        foreach ($this->tables as $table => $constraint) {
            Schema::table($table, function (Blueprint $table) use ($constraint) {
                $table->foreign('usuarioA', $constraint)
                      ->references('idUsuario')
                      ->on('TUsuarios');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $constraint) {
            Schema::table($table, function (Blueprint $table) use ($constraint) {
                $table->dropForeign($constraint);
            });
        }
    }
};
