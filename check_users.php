DB::table('users')
  ->leftJoin('memberships', 'users.id', '=', 'memberships.user_id')
  ->select('users.username', 'memberships.membership_type', 'memberships.status', 'memberships.expires_at')
  ->whereIn('users.username', ['Luna_MX', 'ParejaCDMX2', 'Single_uno'])
  ->get()
  ->each(function($u){ echo $u->username.' | '.$u->membership_type.' | '.$u->status.' | '.$u->expires_at.PHP_EOL; });
