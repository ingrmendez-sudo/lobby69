<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'nickname'     => 'Luna_MX',
                'display_name' => 'Luna',
                'profile_type' => 'single',
                'gender'       => 'femenino',
                'age'          => 28,
                'city'         => 'Ciudad de México',
                'state'        => 'CDMX',
                'orientation'  => 'bisexual',
                'bio'          => 'Amante de la vida, el arte y las conexiones reales.',
                'interests'    => json_encode(['Tríos','Sólo ellas','Cybersexo']),
                'looking_for'  => json_encode(['Parejas bisexuales','Mujeres bisexuales']),
            ],
            [
                'nickname'     => 'ParejaMTY',
                'display_name' => 'Carlos y Diana',
                'profile_type' => 'pareja',
                'gender'       => 'masculino',
                'age'          => 32,
                'partner_name' => 'Diana',
                'partner_age'  => 29,
                'partner_gender' => 'femenino',
                'partner_orientation' => 'bisexual',
                'city'         => 'Monterrey',
                'state'        => 'Nuevo León',
                'orientation'  => 'heterosexual',
                'bio'          => 'Pareja abierta buscando nuevas experiencias.',
                'interests'    => json_encode(['Intercambio completo','Tríos','Mirar y ser vistos']),
                'looking_for'  => json_encode(['Parejas heterosexuales','Unicornios']),
            ],
            [
                'nickname'     => 'Unicornio_GDL',
                'display_name' => 'Valeria',
                'profile_type' => 'unicornio',
                'gender'       => 'femenino',
                'age'          => 25,
                'city'         => 'Guadalajara',
                'state'        => 'Jalisco',
                'orientation'  => 'bisexual',
                'bio'          => 'Libre, curiosa y sin tabúes.',
                'interests'    => json_encode(['Tríos','Intercambio light','Sólo ellas']),
                'looking_for'  => json_encode(['Parejas bisexuales','Parejas (ella bisexual)']),
            ],
            [
                'nickname'     => 'Marco_Puebla',
                'display_name' => 'Marco',
                'profile_type' => 'single',
                'gender'       => 'masculino',
                'age'          => 35,
                'city'         => 'Puebla',
                'state'        => 'Puebla',
                'orientation'  => 'heterosexual',
                'bio'          => 'Sencillo, discreto y respetuoso.',
                'interests'    => json_encode(['Intercambio completo','Relaciones abiertas']),
                'looking_for'  => json_encode(['Parejas heterosexuales','Mujeres heterosexuales']),
            ],
            [
                'nickname'     => 'SofiaQRO',
                'display_name' => 'Sofía',
                'profile_type' => 'single',
                'gender'       => 'femenino',
                'age'          => 30,
                'city'         => 'Querétaro',
                'state'        => 'Querétaro',
                'orientation'  => 'heterosexual',
                'bio'          => 'Curiosa, divertida, buscando conocer personas interesantes.',
                'interests'    => json_encode(['Mirar y ser vistos','Intercambio light','Amistad']),
                'looking_for'  => json_encode(['Parejas heterosexuales','Hombres heterosexuales']),
            ],
            [
                'nickname'     => 'ParejaCDMX2',
                'display_name' => 'Roberto y Ana',
                'profile_type' => 'pareja',
                'gender'       => 'masculino',
                'age'          => 38,
                'partner_name' => 'Ana',
                'partner_age'  => 35,
                'partner_gender' => 'femenino',
                'partner_orientation' => 'bisexual',
                'city'         => 'Ciudad de México',
                'state'        => 'CDMX',
                'orientation'  => 'heterosexual',
                'bio'          => 'Llevamos 5 años en el estilo de vida, discretos y respetuosos.',
                'interests'    => json_encode(['Intercambio completo','Sólo ellas','Cuckold']),
                'looking_for'  => json_encode(['Unicornios','Parejas bisexuales']),
            ],
            [
                'nickname'     => 'Daniela_BCN',
                'display_name' => 'Daniela',
                'profile_type' => 'unicornio',
                'gender'       => 'femenino',
                'age'          => 27,
                'city'         => 'Tijuana',
                'state'        => 'Baja California',
                'orientation'  => 'bisexual',
                'bio'          => 'Fronteriza, open minded, amante de las buenas conversaciones.',
                'interests'    => json_encode(['Tríos','Prácticas BDSM','Compartir fetiches']),
                'looking_for'  => json_encode(['Parejas bisexuales','Mujeres bisexuales']),
            ],
            [
                'nickname'     => 'ParejaManzanillo',
                'display_name' => 'Héctor y Carmen',
                'profile_type' => 'pareja',
                'gender'       => 'masculino',
                'age'          => 42,
                'partner_name' => 'Carmen',
                'partner_age'  => 39,
                'partner_gender' => 'femenino',
                'partner_orientation' => 'heterosexual',
                'city'         => 'Manzanillo',
                'state'        => 'Colima',
                'orientation'  => 'heterosexual',
                'bio'          => 'Costa, mar y buen humor. Buscamos amigos del estilo de vida.',
                'interests'    => json_encode(['Intercambio completo','Mirar y ser vistos','Amistad']),
                'looking_for'  => json_encode(['Parejas heterosexuales','Parejas bisexuales']),
            ],
            [
                'nickname'     => 'Alex_Cancun',
                'display_name' => 'Alex',
                'profile_type' => 'single',
                'gender'       => 'masculino',
                'age'          => 31,
                'city'         => 'Cancún',
                'state'        => 'Quintana Roo',
                'orientation'  => 'bisexual',
                'bio'          => 'Viajero, fotógrafo amateur, sin etiquetas.',
                'interests'    => json_encode(['Intercambio light','Cybersexo','Relaciones abiertas']),
                'looking_for'  => json_encode(['Parejas bisexuales','Mujeres bisexuales']),
            ],
            [
                'nickname'     => 'ParejaGDL',
                'display_name' => 'Luis y Fernanda',
                'profile_type' => 'pareja',
                'gender'       => 'masculino',
                'age'          => 36,
                'partner_name' => 'Fernanda',
                'partner_age'  => 33,
                'partner_gender' => 'femenino',
                'partner_orientation' => 'bisexual',
                'city'         => 'Guadalajara',
                'state'        => 'Jalisco',
                'orientation'  => 'heterosexual',
                'bio'          => 'Tapatíos de corazón, abiertos y sin dramas.',
                'interests'    => json_encode(['Intercambio completo','Tríos','Sólo ellas']),
                'looking_for'  => json_encode(['Unicornios','Parejas bisexuales']),
            ],
        ];

        $now = Carbon::now()->toDateTimeString();

        foreach ($profiles as $data) {
            // Saltar si ya existe el nickname
            $exists = DB::table('profiles')
                ->where('nickname', $data['nickname'])
                ->exists();

            if ($exists) {
                $this->command->info("Saltando {$data['nickname']} — ya existe.");
                continue;
            }

            // Crear usuario ficticio en tabla users
            $userId = (string) Str::uuid();
            DB::table('users')->insert([
                'id'                  => $userId,
                'email'               => strtolower($data['nickname']) . '@test.lobby69.mx',
                'username'            => strtolower($data['nickname']),
                'name'                => $data['display_name'],
                'membership_type'     => 'trial',
                'verification_status' => 'none',
                'active'              => true,
                'password'            => bcrypt('Test1234!'),
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);


            // Crear perfil
            DB::table('profiles')->insert(array_merge([
                'id'                  => (string) Str::uuid(),
                'user_id'             => $userId,
                'country'             => 'México',
                'public'              => true,
                'verified_profile'    => false,
                'profile_completed'   => true,
                'profile_completed_at'=> $now,
                'last_active_at'      => Carbon::now()->subMinutes(rand(5, 2880))->toDateTimeString(),
                'created_at'          => $now,
                'updated_at'          => $now,
                'partner_name'        => null,
                'partner_age'         => null,
                'partner_gender'      => null,
                'partner_orientation' => null,
                'bio'                 => '',
                'interests'           => json_encode([]),
                'looking_for'         => json_encode([]),
            ], $data));

            $this->command->info("Creado: {$data['nickname']}");
        }

        $this->command->info('✅ Seeder completado.');
    }
}
