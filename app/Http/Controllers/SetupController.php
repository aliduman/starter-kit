<?php

namespace App\Http\Controllers;

use App\Support\ResponseCache\ResponseCacheInvalidator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * SSH'sız hostta deploy sonrası adım (CPANEL_DEPLOY.md): migrate, isteğe bağlı seed, storage:link, cache temizliği.
 *
 * Güvenlik: SETUP_TOKEN boşsa (varsayılan) yok sayılır, 404 döner; token sabit zamanlı karşılaştırılır, istek dakikada
 * 5 ile sınırlıdır. Production'da seed yalnızca SETUP_ADMIN_EMAIL/SETUP_ADMIN_PASSWORD verilmişse admin oluşturur
 * (DatabaseSeeder); bilinen varsayılan şifreli hesap açılmaz. İşi bitince SETUP_TOKEN'ı .env'den silin.
 */
class SetupController extends Controller
{
    public function __invoke(Request $request, ResponseCacheInvalidator $responseCache, string $token): Response
    {
        $expected = (string) config('app.setup_token');

        abort_if($expected === '' || ! hash_equals($expected, $token), 404);

        $output = [];

        Artisan::call('migrate', ['--force' => true]);
        $output[] = "== migrate ==\n".Artisan::output();

        if ($request->boolean('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $output[] = "== seed ==\n".Artisan::output();
        }

        try {
            Artisan::call('storage:link');
            $output[] = "== storage:link ==\n".Artisan::output();
        } catch (Throwable $e) {
            $output[] = '== storage:link HATA (hosting symlink engelliyor olabilir): '.$e->getMessage();
        }

        // Cache'teki sayfalar eski public/build dosyalarına işaret eder; her deploy'da temizlenmeli.
        $responseCache->clear();

        if ($request->boolean('cache')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $output[] = '== config/route/view cache oluşturuldu ==';
        } else {
            Artisan::call('optimize:clear');
            $output[] = '== tüm cacheler temizlendi (dondurmak için ?cache=1) ==';
        }

        return response('<pre>'.e(implode("\n\n", $output)).'</pre>')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }
}
