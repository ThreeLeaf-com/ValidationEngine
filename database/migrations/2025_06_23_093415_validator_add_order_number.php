<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ThreeLeaf\ValidationEngine\Models\Validator;

return new class extends Migration {

    /** Run the migrations. */
    public function up(): void
    {
        Schema::table(Validator::TABLE_NAME, function (Blueprint $table) {
            $table->integer('order_number')->default(0)->after('context')->comment('Order number for sorting validators');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::table(Validator::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
