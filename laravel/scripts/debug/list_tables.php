<?php

use Illuminate\Support\Facades\DB;

$tables = DB::select('SELECT name FROM sqlite_master WHERE type="table"');
foreach ($tables as $table) {
    echo $table->name."\n";
}
