<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The home page used to fall back to the designed questions in
     * lang/fa/home.php while the table was empty, so they were visible on
     * the site but not in the admin. Move them into the table once (only
     * when it is still empty) so the admin manages exactly what is shown.
     * Questions still waiting for an answer keep an empty answer.
     */
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });

        if (DB::table('faqs')->exists()) {
            return;
        }

        $items = [
            ['توکن‌سازی دارایی چیست و چه فایده‌ای برای من دارد؟', 'در توکن‌سازی، مالکیت یک دارایی واقعی مثل یک پروژه‌ی صنعتی یا تجاری به واحدهای دیجیتال کوچک تقسیم می‌شود؛ به این ترتیب می‌توانید با سرمایه‌ی کم در طرح‌های بزرگ شریک شوید و سهم خود را شفاف دنبال کنید.'],
            ['حداقل مبلغ سرمایه‌گذاری در پایدار فاند چقدر است؟', null],
            ['گزارش‌ها و سود طرح چگونه به من اعلام می‌شود؟', null],
            ['آیا می‌توانم پیش از پایان طرح، توکن‌هایم را بفروشم؟', null],
            ['دارایی پشتوانه‌ی هر طرح چگونه نظارت می‌شود؟', null],
        ];

        $now = now();
        DB::table('faqs')->insert(array_map(fn (array $item, int $index) => [
            'question' => $item[0],
            'answer' => $item[1],
            'sort_order' => $index + 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $items, array_keys($items)));
    }

    /** The imported rows stay; they are content now. */
    public function down(): void
    {
        DB::table('faqs')->whereNull('answer')->update(['answer' => '']);

        Schema::table('faqs', function (Blueprint $table) {
            $table->text('answer')->nullable(false)->change();
        });
    }
};
