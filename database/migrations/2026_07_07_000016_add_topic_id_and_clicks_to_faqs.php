<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->foreignId('faq_topic_id')->nullable()->constrained('faq_topics')->nullOnDelete();
            $table->integer('clicks')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign(['faq_topic_id']);
            $table->dropColumn(['faq_topic_id', 'clicks']);
        });
    }
};
