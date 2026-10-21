<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Yazılar ortak kategori/etiket tablolarını kullanır (categories type=media, taggables); SEO alanları kolon olur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_articles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('author_id')->constrained('categories')->nullOnDelete();
            $table->string('meta_title')->nullable()->after('published_at');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'tags']);
        });
    }

    public function down(): void
    {
        Schema::table('media_articles', function (Blueprint $table) {
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['meta_title', 'meta_description']);
        });
    }
};
