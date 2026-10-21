<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Settings\SettingFields;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/** Site Ayarları: alanlar App\Support\Settings\SettingFields'tan, gruplar bölüm olarak gelir. */
class ManageSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Site Yönetimi';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site Ayarları';

    protected static ?string $slug = 'settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('manage-site');
    }

    public function mount(): void
    {
        $this->form->fill(collect(app(SettingFields::class)->all())
            ->map(fn (array $field, string $key) => $field['type'] === 'toggle'
                ? (bool) Setting::get($key)
                : Setting::get($key))
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        $fields = app(SettingFields::class);
        $sections = [];

        foreach ($fields->groups() as $group => $label) {
            $components = collect($fields->all())
                ->filter(fn (array $field) => $field['group'] === $group)
                ->map(fn (array $field, string $key) => static::field($key, $field))
                ->values()
                ->all();

            if ($components !== []) {
                $sections[] = Section::make($label)->schema($components)->columns(2);
            }
        }

        return $schema->statePath('data')->components($sections);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Kaydet')->submit('save')->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $fields = app(SettingFields::class);

        Setting::store(collect($this->form->getState())
            ->map(fn (mixed $value, string $key) => match (true) {
                ($fields->get($key)['type'] ?? null) === 'toggle' => $value ? '1' : '0',
                blank($value) => null,
                default => (string) $value,
            })
            ->all());

        Notification::make()->success()->title('Ayarlar kaydedildi.')->send();
    }

    private static function field(string $key, array $field): Field
    {
        $component = match ($field['type']) {
            'toggle' => Toggle::make($key),
            'select' => Select::make($key)->options($field['options'] ?? []),
            'textarea' => Textarea::make($key)->rows(3)->columnSpanFull(),
            'url' => TextInput::make($key)->url(),
            'email' => TextInput::make($key)->email(),
            default => TextInput::make($key),
        };

        return $component->label($field['label'] ?? $key)->helperText($field['help'] ?? null);
    }
}
