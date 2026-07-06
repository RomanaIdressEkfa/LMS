<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

/**
 * Addon system API — list modules and flip them on/off.
 */
class ModuleController extends Controller
{
    public function index()
    {
        return response()->json([
            'modules' => Module::orderBy('sort_order')->get(),
        ]);
    }

    public function toggle(Request $request, int $id)
    {
        $module = Module::findOrFail($id);

        if ($module->is_core) {
            return response()->json(['message' => 'Core modules cannot be disabled.'], 422);
        }

        $module->update(['enabled' => ! $module->enabled]);

        return response()->json(['module' => $module]);
    }
}
