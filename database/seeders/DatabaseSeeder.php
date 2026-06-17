<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UsuarioSeeder::class);
        $this->call(SucursalSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(ActividadSeeder::class);
        $this->call(MarcaSeeder::class);
        $this->call(EmpleadoSeeder::class);
        $this->call(HorarioLaboralSeeder::class);
        $this->call(ControlAsistenciaSeeder::class);
        $this->call(EsquemaSueldoSeeder::class);
        $this->call(SocioSeeder::class);
        $this->call(MembresiaSeeder::class);
        $this->call(ControlAccesoSeeder::class);
        $this->call(PenalizacionSeeder::class);
        $this->call(NotificacionSeeder::class);
        $this->call(ClaseGrupalSeeder::class);
        $this->call(ReservaSeeder::class);
        $this->call(CajaSeeder::class);
        $this->call(ReciboSeeder::class);
        $this->call(DetalleMetodoPagoSeeder::class);
        $this->call(EquipamientoSeeder::class);
        $this->call(MantenimientoPreventivoSeeder::class);
        $this->call(ReporteFallaSeeder::class);
    }
}
