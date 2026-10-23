<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Edit the site wide settings.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'app_name' => Setting::get('app.name', config('app.name')),
                'allow_registration' => filter_var(
                    Setting::get('app.allow_registration', '1'),
                    FILTER_VALIDATE_BOOLEAN,
                ),
                'support_email' => Setting::get('app.support_email', config('mail.from.address')),
                'seo' => [
                    'title' => Setting::get('seo.title', config('app.name')),
                    'description' => Setting::get('seo.description'),
                    'keywords' => Setting::get('seo.keywords'),
                    'robots' => Setting::get('seo.robots', 'index, follow'),
                    'canonical_url' => Setting::get('seo.canonical_url'),
                    'og_title' => Setting::get('seo.og_title'),
                    'og_description' => Setting::get('seo.og_description'),
                    'og_image' => Setting::get('seo.og_image'),
                    'og_type' => Setting::get('seo.og_type', 'website'),
                    'twitter_card' => Setting::get('seo.twitter_card', 'summary_large_image'),
                    'twitter_site' => Setting::get('seo.twitter_site'),
                    'twitter_creator' => Setting::get('seo.twitter_creator'),
                ],
            ],
        ]);
    }

    /**
     * Store the site wide settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:60'],
            'allow_registration' => ['boolean'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'seo' => ['array'],
            'seo.title' => ['nullable', 'string', 'max:70'],
            'seo.description' => ['nullable', 'string', 'max:160'],
            'seo.keywords' => ['nullable', 'string', 'max:255'],
            'seo.robots' => ['nullable', 'string', 'max:60'],
            'seo.canonical_url' => ['nullable', 'url', 'max:255'],
            'seo.og_title' => ['nullable', 'string', 'max:95'],
            'seo.og_description' => ['nullable', 'string', 'max:200'],
            'seo.og_image' => ['nullable', 'url', 'max:255'],
            'seo.og_type' => ['nullable', 'string', 'max:40'],
            'seo.twitter_card' => ['nullable', 'string', 'max:40'],
            'seo.twitter_site' => ['nullable', 'string', 'max:40'],
            'seo.twitter_creator' => ['nullable', 'string', 'max:40'],
        ]);

        Setting::set('app.name', $validated['app_name']);
        Setting::set('app.allow_registration', $request->boolean('allow_registration') ? '1' : '0');
        Setting::set('app.support_email', $validated['support_email'] ?? null);

        foreach ($validated['seo'] ?? [] as $key => $value) {
            Setting::set('seo.'.$key, $value);
        }

        AuditLog::record('settings.updated');

        Inertia::flash('toast', ['type' => 'success', 'message' => 'The settings were saved.']);

        return back();
    }
}
