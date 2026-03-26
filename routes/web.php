<?php

use App\Http\Controllers\HomeController;
use App\Mail\ContactFormMail;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    return view('under-construction');
})->name('home');

Route::get('/welcome', HomeController::class)->name('home');

Route::post('/contacto/enviar', function (Request $request) {
    if (filled((string) $request->input('website'))) {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->to(url('/welcome').'#contacto')->with('contact_sent', true);
    }

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:60'],
        'message' => ['required', 'string', 'max:4000'],
        'started_at' => ['required', 'integer', 'min:0'],
    ]);

    $rateLimitKey = 'contact:'.sha1($request->ip().'|'.strtolower((string) $validated['email']));
    if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
        throw ValidationException::withMessages([
            'contact' => 'Se han enviado demasiadas solicitudes. Intenta nuevamente en unos minutos.',
        ]);
    }
    RateLimiter::hit($rateLimitKey, 600);

    $elapsedMs = (int) round(microtime(true) * 1000) - (int) $validated['started_at'];
    if ($elapsedMs < 2500) {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->to(url('/welcome').'#contacto')->with('contact_sent', true);
    }

    $settings = SiteSetting::query()->first();
    $toRaw = trim((string) ($settings?->contact_form_to_emails ?? ''));
    $bccRaw = trim((string) ($settings?->contact_form_bcc_emails ?? ''));

    $parseEmails = function (string $raw): array {
        $parts = preg_split('/[,\n;\r]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $emails = collect($parts)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL))
            ->map(fn ($value) => strtolower($value))
            ->unique()
            ->values()
            ->all();

        return $emails;
    };

    $to = $parseEmails($toRaw);
    $bcc = $parseEmails($bccRaw);

    if (count($to) === 0) {
        throw ValidationException::withMessages([
            'contact' => 'No hay correos de recepción configurados en el panel administrativo.',
        ]);
    }

    $mailer = Mail::to($to);
    if (count($bcc) > 0) {
        $mailer->bcc($bcc);
    }

    $mailer->send(new ContactFormMail(
        name: trim((string) $validated['name']),
        email: trim((string) $validated['email']),
        phone: trim((string) ($validated['phone'] ?? '')) !== '' ? trim((string) $validated['phone']) : null,
        messageBody: trim((string) $validated['message']),
        submittedAt: now()->format('d/m/Y H:i'),
        ipAddress: $request->ip(),
        userAgent: trim((string) $request->userAgent()) !== '' ? (string) $request->userAgent() : null,
    ));

    if ($request->expectsJson()) {
        return response()->json(['ok' => true]);
    }

    return redirect()->to(url('/welcome').'#contacto')->with('contact_sent', true);
})->name('contact.send');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin')) {
            return redirect()->route('admin.about');
        }

        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/nosotros');

    Route::livewire('productos', 'pages::admin.products')->name('admin.products');
    Route::livewire('galeria', 'pages::admin.gallery')->name('admin.gallery');
    Route::livewire('nosotros', 'pages::admin.about')->name('admin.about');
    Route::livewire('sitio', 'pages::admin.site')->name('admin.site');
});

require __DIR__.'/settings.php';
