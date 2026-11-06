<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Paneldeki SEO analizinin odak anahtar kelimesi (App\Support\Seo\Analysis). */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('announcements', 'focus_keyword')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->string('focus_keyword', 100)->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('announcements', 'focus_keyword')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->dropColumn('focus_keyword'));
        }
    }
};
