<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Şahid'deki gibi SEO alanları ayrı kolonlarda (JSON `seo` yerine). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('corporate_pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('published_at');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->dropColumn('seo');
        });
    }

    public function down(): void
    {
        Schema::table('corporate_pages', function (Blueprint $table) {
            $table->json('seo')->nullable();
            $table->dropColumn(['meta_title', 'meta_description']);
        });
    }
};
