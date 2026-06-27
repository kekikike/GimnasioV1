<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Bug 1: Membership Sync Test ===\n\n";

// Test 1: SP should not change anything since state is already correct
echo "Test 1: Running SP with current state (should be unchanged)...\n";
DB::statement('CALL sp_TMembresias_SincronizarEstado()');
$mems = DB::table('TMembresias')->select('idMembresia','carnetSocio','estadoMembresia','fechaFinMembresia')->get();
foreach ($mems as $m) {
    echo "  #{$m->idMembresia} Socio:{$m->carnetSocio} Estado:{$m->estadoMembresia} Fin:{$m->fechaFinMembresia}\n";
}

// Test 2: Simulate time passing - set 6700001 end date to yesterday
echo "\nTest 2: Setting membership #1 end date to yesterday (should become Vencida)...\n";
DB::table('TMembresias')->where('idMembresia', 1)->update(['fechaFinMembresia' => '2026-06-26']);
$before = DB::table('TMembresias')->where('idMembresia', 1)->value('estadoMembresia');
echo "  Before sync: {$before}\n";

DB::statement('CALL sp_TMembresias_SincronizarEstado()');
$after = DB::table('TMembresias')->where('idMembresia', 1)->value('estadoMembresia');
echo "  After sync: {$after}\n";
echo "  " . ($after === 'Vencida' ? "PASS" : "FAIL") . "\n";

// Test 3: Restore and verify it reactivates
echo "\nTest 3: Restoring membership #1 end date to future (should reactivate)...\n";
DB::table('TMembresias')->where('idMembresia', 1)->update(['fechaFinMembresia' => '2026-06-30', 'estadoMembresia' => 'Vencida']);
$before2 = DB::table('TMembresias')->where('idMembresia', 1)->value('estadoMembresia');
echo "  Before sync: {$before2}\n";

DB::statement('CALL sp_TMembresias_SincronizarEstado()');
$after2 = DB::table('TMembresias')->where('idMembresia', 1)->value('estadoMembresia');
echo "  After sync: {$after2}\n";
echo "  " . ($after2 === 'Activa' ? "PASS" : "FAIL") . "\n";

// Test 4: Check audit log
echo "\nTest 4: Audit log entries for TMembresias estadoMembresia changes...\n";
$audits = DB::table('TAuditorias')
    ->where('tablaNombre', 'TMembresias')
    ->where('campo', 'estadoMembresia')
    ->orderBy('fechaA', 'desc')
    ->limit(5)
    ->get();
echo "  Found " . count($audits) . " audit entries\n";
foreach ($audits as $a) {
    echo "    #{$a->registroId}: {$a->valorAnterior} -> {$a->valorNuevo} ({$a->detalles})\n";
}

echo "\nBug 1 tests completed.\n";
