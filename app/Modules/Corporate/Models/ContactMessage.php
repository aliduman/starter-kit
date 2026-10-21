<?php

namespace App\Modules\Corporate\Models;

use App\Models\Concerns\LogsActivity;
use App\Support\Modules;
use Illuminate\Database\Eloquent\Model;

/** İletişim formundan gelen mesaj; panelde "Gelen Mesajlar". Konu seçenekleri config/modules.php `contact_subjects`. */
class ContactMessage extends Model
{
    use LogsActivity;

    public const STATUSES = [
        'new' => 'Yeni',
        'read' => 'Okundu',
        'archived' => 'Arşivlendi',
    ];

    protected $table = 'corporate_contact_messages';

    protected $fillable = ['name', 'email', 'subject', 'message', 'consented_at', 'status'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }

    /** @return array<string, string> */
    public static function subjects(): array
    {
        return Modules::get('corporate')['contact_subjects'] ?? [];
    }
}
