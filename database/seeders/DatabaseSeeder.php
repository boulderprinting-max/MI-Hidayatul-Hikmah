<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin_sekolah',
            'guru_kelas_1',
            'guru_kelas_2',
            'guru_kelas_3',
            'guru_kelas_4',
            'guru_kelas_5',
            'guru_kelas_6',
            'wali_murid',
            'siswa',
        ];

        foreach ($roles as $roleName) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@mihidayatulhikmah.sch.id',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin_sekolah@mihidayatulhikmah.sch.id',
                'password' => bcrypt('password'),
                'role' => 'admin_sekolah',
            ],
            [
                'name' => 'Guru Kelas 1',
                'email' => 'guru1@mihidayatulhikmah.sch.id',
                'password' => bcrypt('password'),
                'role' => 'guru_kelas_1',
            ],
            [
                'name' => 'Wali Murid',
                'email' => 'wali@mihidayatulhikmah.sch.id',
                'password' => bcrypt('password'),
                'role' => 'wali_murid',
            ],
            [
                'name' => 'Siswa',
                'email' => 'siswa@mihidayatulhikmah.sch.id',
                'password' => bcrypt('password'),
                'role' => 'siswa',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );
            // Sync roles to avoid duplicate assignments
            $user->syncRoles([$userData['role']]);
        }

        \App\Models\PengaturanSekolah::firstOrCreate(
            ['nama_sekolah' => 'MI Hidayatul Hikmah'],
            [
                'nss' => '111232040082',
                'npsn' => '60706243',
                'alamat' => 'Jl. Raya Hidayatul Hikmah No. 45, Cirebon, Jawa Barat',
                'telepon' => '0231-123456',
                'email' => 'info@mihidayatulhikmah.sch.id',
                'website' => 'mihidayatulhikmah.sch.id',
                'kepala_sekolah' => 'H. Ahmad Syarifuddin, S.Pd.I',
                'nip_kepala_sekolah' => '197508122005011002',
            ]
        );
    }
}
