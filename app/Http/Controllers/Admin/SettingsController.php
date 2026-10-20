<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = Setting::where('group', 'general')->pluck('value', 'key');
        return view('admin.settings.general', compact('settings'));
    }

    public function saveGeneral(Request $request)
    {
        $data = $request->validate([
            'site_name'         => 'required|string|max:80',
            'site_tagline'      => 'nullable|string|max:200',
            'site_email'        => 'nullable|email|max:150',
            'site_phone'        => 'nullable|string|max:30',
            'site_address'      => 'nullable|string|max:255',
            'site_city'         => 'nullable|string|max:80',
            'whatsapp'          => 'nullable|string|max:30',
            'free_shipping_min' => 'nullable|numeric|min:0',
            'shipping_charge'   => 'nullable|numeric|min:0',
            'prescription_validity_days' => 'nullable|integer|min:1|max:365',
            'currency'          => 'nullable|string|max:10',
            'site_logo'         => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
        ]);

        foreach (collect($data)->except('site_logo') as $field => $value) {
            Setting::set($field, $value, 'general');
        }

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $fn   = 'logo.' . $file->extension();
            $file->move(public_path('images'), $fn);
            Setting::set('site_logo', $fn, 'general');
        }

        return redirect()->route('admin.settings.general')
            ->with('success', 'Settings saved successfully!');
    }

    public function appearance()
    {
        $settings = Setting::where('group', 'appearance')->pluck('value', 'key');
        return view('admin.settings.appearance', compact('settings'));
    }

    public function saveAppearance(Request $request)
    {
        $fields = [
            'primary_color', 'accent_color', 'header_bg',
            'footer_bg', 'announcement_text', 'show_announcement'
        ];

        foreach ($fields as $field) {
            Setting::set($field, $request->$field, 'appearance');
        }

        return redirect()->route('admin.settings.appearance')
            ->with('success', 'Appearance saved!');
    }
}
