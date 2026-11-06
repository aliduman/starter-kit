<?php

use App\Filament\Resources\ActivityResource;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\Support\UntranslatedPanelLabels;

use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\seed;

/*
 * Arayüzde İngilizce kalan metinleri yakalar: çerçevenin ve paketlerin çevirisi eksik satırları, paneldeki etiketi
 * verilmemiş alanları ve işlem geçmişinde Türkçe adı olmayan kayıt türlerini ve alanları. Her sitede aynı dosyadır.
 */

// Sunucuda yedek dil de tr olabilir; o zaman eksik bir çeviri anahtarın kendisi olarak (pagination.next) görünür.
beforeEach(function () {
    app()->setLocale('tr');
    app('translator')->setFallback('tr');
});

/** Paketlerin Türkçe dosyasında İngilizceyle aynı kalması doğru olan satırlar (dosya adları, yazı yönü). */
const ENGLISH_IS_FINE = [
    'filament-actions::export.file_name',
    'filament-actions::import.example_csv.file_name',
    'filament-actions::import.failure_csv.file_name',
    'filament-actions::import.failure_csv.error_header',
    'filament-panels::layout.direction',
    'filament-query-builder::query-builder.operators.number.aggregates.min.summary',
];

/** @return array<string, array<string, mixed>> Çeviri kaynağı ("" = çerçeve) => satır => İngilizce metin */
function englishLines(): array
{
    $sources = ['' => base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'), ...app('translator')->getLoader()->namespaces()];
    $lines = [];

    foreach ($sources as $namespace => $path) {
        foreach (glob("{$path}/en/*.php") as $file) {
            foreach (Arr::dot(require $file) as $key => $english) {
                $lines[$namespace][($namespace ? "{$namespace}::" : '').basename($file, '.php').".{$key}"] = $english;
            }
        }
    }

    return $lines;
}

/** @return list<class-string> İşlem geçmişine yazılan modeller */
function loggedModels(): array
{
    return collect(glob(app_path('{Models,Modules/*/Models}/*.php'), GLOB_BRACE))
        ->map(fn (string $file) => 'App\\'.str_replace(['/', '.php'], ['\\', ''], Str::after($file, app_path().'/')))
        ->filter(fn (string $class) => in_array(LogsActivity::class, class_uses_recursive($class), true))
        ->values()
        ->all();
}

it('has a Turkish line for every English line of the framework and the packages', function () {
    $missing = collect(englishLines())->flatMap(fn (array $lines) => array_keys($lines))
        // Örnek satırlar (validation.custom.attribute-name...) çevrilmez.
        ->reject(fn (string $line) => str_starts_with($line, 'validation.custom.') || Lang::hasForLocale($line, 'tr'))
        ->values();

    expect($missing->all())->toBe([]);
});

it('leaves no package line in English', function () {
    $english = collect(englishLines())->except('')->collapse()
        // Birim ve simgeler ("px", "X", "URL", ", ") Türkçede de aynıdır; yalnızca küçük harfli bir kelime içeren satır sayılır.
        ->filter(fn ($text, string $line) => is_string($text) && preg_match('/[a-z]{3,}/', strip_tags($text))
            && __($line, [], 'tr') === $text && ! in_array($line, ENGLISH_IS_FINE, true))
        ->keys();

    // Eksik ya da İngilizce kalmış satırlar lang/vendor/{paket}/tr altında düzeltilir.
    expect($english->all())->toBe([]);
});

it('gives every panel field, column, filter and action a Turkish label', function () {
    seed(RolesAndPermissionsSeeder::class);
    $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
    $admin->assignRole('super-admin');

    // Etiketi verilmemiş bileşene Filament kod adından İngilizce etiket üretir; ->label('...') ile düzeltilir.
    expect(UntranslatedPanelLabels::find($admin))->toBe([]);
});

it('names every logged record type and field in Turkish', function () {
    $untranslated = [];

    foreach (loggedModels() as $class) {
        // Kayıt türünün adı paneldeki kaynaktan ya da modelin $activityLabel özelliğinden gelir.
        if (! Filament::getModelResource($class) && ! property_exists($class, 'activityLabel')) {
            $untranslated[] = "{$class}: kaynağı yok, \$activityLabel verin";
        }

        $model = new $class;
        $ignored = [...$model->getHidden(), ...(property_exists($model, 'activityIgnored') ? (fn () => $this->activityIgnored)->call($model) : [])];

        foreach (array_diff($model->getFillable(), $ignored) as $field) {
            if (ActivityResource::fieldLabel($field, $class) === $field) {
                $untranslated[] = "{$class}: {$field} (panel formunda ya da validation.attributes içinde adı yok)";
            }
        }
    }

    expect($untranslated)->toBe([]);
});

it('shows validation messages, pagination and error pages in Turkish', function () {
    expect(Validator::make([], ['email' => 'required'])->errors()->first('email'))->toStartWith('E-posta ')->not->toContain('field')
        ->and(__('pagination.next'))->toBe('Sonraki &raquo;');

    // Sitenin adres kuralı (ör. sondaki eğik çizgi) önce yönlendirebilir.
    followingRedirects()->get('/bu-adres-yok')->assertNotFound()->assertSee('Sayfa bulunamadı');
});
