<?php
$users = DB::table('users')
    ->whereIn('username', ['Luna_MX', 'ParejaCDMX2', 'Single_uno'])
    ->select('id', 'username', 'membership_type', 'email')
    ->get();
foreach ($users as $u) {
    echo $u->username . ' | ' . ($u->membership_type ?? 'NULL') . ' | id=' . $u->id . PHP_EOL;
}
