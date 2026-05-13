<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('alter table invoices alter column payment_method type varchar(60) using payment_method::varchar(60)');
            DB::statement('alter table payments alter column method type varchar(60) using method::varchar(60)');
            return;
        }

        DB::statement('alter table invoices modify payment_method varchar(60) null');
        DB::statement('alter table payments modify method varchar(60) not null');
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('alter table invoices alter column payment_method type varchar(20) using payment_method::varchar(20)');
            DB::statement('alter table payments alter column method type varchar(20) using method::varchar(20)');
            return;
        }

        DB::statement('alter table invoices modify payment_method varchar(20) null');
        DB::statement('alter table payments modify method varchar(20) not null');
    }
};
