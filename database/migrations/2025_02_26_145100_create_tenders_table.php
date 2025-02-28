<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('file')->nullable();
            $table->string('store_doc_id')->nullable();
            $table->string('selected_store_doc_id')->nullable();
            $table->string('partner_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('currency_rate')->nullable();
            $table->string('document_mode')->nullable();
            $table->string('doc_no')->nullable();
            $table->string('doc_no_serial')->nullable();
            $table->dateTime('doc_date')->nullable();
            $table->string('amount_cash')->nullable();
            $table->string('amount_cash2')->nullable();
            $table->string('amount_card')->nullable();
            $table->string('amount_credit')->nullable();
            $table->string('amount_gift')->nullable();
            $table->string('amount_gift2')->nullable();
            $table->string('amount_tender_cash')->nullable();
            $table->string('amount_tender_card')->nullable();
            $table->string('amount_tender_credit')->nullable();
            $table->string('amount_tender_gift')->nullable();
            $table->string('amount_tender_gift2')->nullable();
            $table->string('tender_discount')->nullable();
            $table->string('last_receipt_no')->nullable();
            $table->string('comment')->nullable();
            $table->string('invoice_only')->nullable();
            $table->string('dim1')->nullable();
            $table->string('dim2')->nullable();
            $table->string('dim3')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
