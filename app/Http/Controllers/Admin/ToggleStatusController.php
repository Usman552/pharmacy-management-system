<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\Request;

class ToggleStatusController extends Controller
{
    public function toggle(Request $request)
    {
        $modelMap = [
            'product'  => Product::class,
            'category' => Category::class,
            'banner'   => Banner::class,
            'user'     => User::class,
        ];

        $modelClass = $modelMap[$request->model] ?? null;

        if (!$modelClass) {
            return response()->json(['error' => 'Invalid model'], 422);
        }

        $record = $modelClass::findOrFail($request->id);
        $record->update(['is_active' => (bool)$request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
