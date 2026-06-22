<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $enabled = config('app.caja_test_mode', true);
        return view('admin.settings.caja', ['enabled' => $enabled]);
    }

    public function toggle(Request $request)
    {
        $request->validate(['enabled' => 'required|boolean']);
        $enabled = (bool) $request->enabled;

        $path = storage_path('app/caja_test_mode.txt');
        try {
            file_put_contents($path, $enabled ? '1' : '0');
            // Update runtime config so changes apply immediately
            config(['app.caja_test_mode' => $enabled]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }
}
