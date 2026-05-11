<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Crisis;
use App\Models\CrisisReport;
use App\Models\Donation;
use App\Models\Lecturer;
use App\Models\NextOfKin;
use App\Models\PublicUser;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. DEMO ADMIN (login: admin@iium.edu.my / password)
        // ============================================================
        Admin::create([
            'admin_name'  => 'Dr. Azmi',
            'email'       => 'puffytiramisu@gmail.com',
            'role'        => 'super_admin',
            'active'      => true,
            'permissions' => ['verify_crisis','verify_death','trigger_ldms','manage_donations','view_blockchain'],
            'password'    => Hash::make('password'),
        ]);

        Admin::factory(2)->create();

        // ============================================================
        // 2. LECTURERS (15 realistic IIUM lecturers)
        // ============================================================
        $lecturerSeeds = [
            // ['Akram',     'Mohd Zeki',  'akram.zeki@iium.edu.my',     'KICT - Computer Science'],
            // ['Murni',     'Mahmud',     'murni@iium.edu.my',          'KICT - Information Systems'],
            // ['Suriani',   'Sulaiman',   'suriani@iium.edu.my',        'KICT - Information Systems'],
            // ['Amelia',    'Ritahani',   'amelia@iium.edu.my',         'KICT - Computer Science'],
            // ['Asadullah', 'Shah',       'asadullah@iium.edu.my',      'KICT - Computer Science'],
            // ['Normaziah', 'Abdul Aziz', 'naa@iium.edu.my',            'KICT - Computer Science'],
            // ['Ahmad',     'Anwar Zainuddin','ahmadanwar@iium.edu.my', 'KICT - Information Systems'],
            // ['Lili',      'Marziana',   'lili@iium.edu.my',           'KICT - Computer Science'],
            // ['Raini',     'Hassan',     'hraini@iium.edu.my',         'KICT - Information Systems'],
            // ['Adamu',     'Abubakar',   'adamu@iium.edu.my',          'KICT - Computer Science'],
            // ['Mohd Izzuddin','Mohd Tamrin','izzuddin@iium.edu.my',    'KOE - Electrical'],
            // ['Salina',    'Kassim',     'ksalina@iium.edu.my',        'KENMS - Economics'],
            // ['Nor Azlina','Aziz',       'norazlina@iium.edu.my',      'KOL - Civil Law'],
            // ['Hafiz',     'Yahya',      'hafiz.yahya@iium.edu.my',    'KOE - Mechatronics'],
            // ['Roszymah',  'Hamzah',     'roszymah@iium.edu.my',       'KOED - Education'],
                ['Amy',     'Amysha Qistina',  'puffypuff@gmail.com', 'KICT - Information Systems'],
        ];

        foreach ($lecturerSeeds as [$fn, $ln, $em, $dept]) {
            Lecturer::create([
                'first_name' => $fn,
                'last_name'  => $ln,
                'email'      => $em,
                'department' => $dept,
                'password'   => Hash::make('password'),
            ]);
        }

        // ============================================================
        // 3. DEMO STUDENT (login: 2225498 / password)
        // ============================================================
        $demoStudent = Student::create([
            'student_id'        => '2225998',
            'first_name'        => 'Nabilah',
            'last_name'         => 'Nordin',
            'email'             => 'nabilahahmad.nordin@live.iium.edu.my',
            'kulliyyah'         => 'Kulliyyah of Information & Communication Technology',
            'programme'         => 'Bachelor of Information Systems',
            'year_of_study'     => '4',
            'mahallah'          => 'Mahallah Hafsah',
            'phone'             => '+60196511239',
            'gender'            => 'Female',
            'nationality'       => 'Malaysian',
            'date_of_birth'     => '2002-08-20',
            'enrollment_status' => 'Active',
            'emergency_contact' => '+60136798284',
            'imaalum_synced_at' => now(),
            'status'            => 'active',
            'password'          => Hash::make('password'),
        ]);

        Student::factory(20)->create();

        // ============================================================
        // 4. DEMO NEXT OF KIN (login: nok@example.com / password)
        // ============================================================
        NextOfKin::create([
            'student_id'                  => $demoStudent->student_id,
            'first_name'                  => 'Rahman',
            'last_name'                   => 'bin Abdullah',
            'relationship_to_student'     => 'Father',
            'email'                       => 'amyshaqistina17@gmail.com',
            'phone'                       => '+60136798284',
            'access_level'                => 'full',
            'emergency_contact_verified'  => true,
            'consent_date'                => now()->subMonths(6),
            'expiry_date'                 => now()->addYears(2),
            'password'                    => Hash::make('password'),
        ]);

        Student::where('student_id', '!=', $demoStudent->student_id)
            ->take(15)->get()->each(function ($student) {
                NextOfKin::factory()->create(['student_id' => $student->student_id]);
            });

        // ============================================================
        // 5. PUBLIC USERS
        // ============================================================
        PublicUser::factory(30)->create();

        // ============================================================
        // 6. CRISIS CASES — 4 active ones for public dashboard
        // ============================================================
        $crisisCases = [
            [
                'crisis_type'        => 'accident',
                'crisis_description' => 'Student involved in severe motorcycle accident. Currently in ICU with multiple injuries requiring urgent medical procedures and extended hospitalization.',
                'crisis_details'     => 'Multiple fractures, head injury. Family unable to cover medical bills.',
                'impact_level'       => 'critical',
                'location'           => 'Gombak Campus, Selangor',
                'donation_target'    => 50000,
                'donation_raised'    => 32500,
            ],
            [
                'crisis_type'        => 'family_emergency',
                'crisis_description' => "Student's parent passed away unexpectedly. Family facing financial hardship for funeral expenses and ongoing educational support.",
                'crisis_details'     => 'Sole breadwinner of family lost. Two younger siblings still in school.',
                'impact_level'       => 'high',
                'location'           => 'Kuantan, Pahang',
                'donation_target'    => 30000,
                'donation_raised'    => 28750,
            ],
            [
                'crisis_type'        => 'natural_disaster',
                'crisis_description' => 'Family home destroyed by severe flooding in Pahang. Urgent shelter and recovery support needed.',
                'crisis_details'     => 'Loss of belongings, displaced family, student unable to continue studies without assistance.',
                'impact_level'       => 'high',
                'location'           => 'Temerloh, Pahang',
                'donation_target'    => 25000,
                'donation_raised'    => 14200,
            ],
            [
                'crisis_type'        => 'illness',
                'crisis_description' => 'Student diagnosed with severe illness requiring long-term treatment and ongoing medical support.',
                'crisis_details'     => 'Treatment expected to last 6-12 months. Currently in induced coma.',
                'impact_level'       => 'critical',
                'location'           => 'Kuala Lumpur',
                'donation_target'    => 75000,
                'donation_raised'    => 19500,
            ],
        ];

        $students = Student::take(4)->get();
        foreach ($crisisCases as $i => $data) {
            $crisis = Crisis::create(array_merge($data, [
                'date_reported' => now()->subDays(rand(5, 30)),
                'status'        => 'active',
                'student_id'    => $students[$i]->student_id ?? null,
            ]));

            CrisisReport::create([
                'student_id'         => $students[$i]->student_id,
                'crisis_id'          => $crisis->crisis_id,
                'report_description' => $crisis->crisis_description,
                'report_status'      => 'verified',
                'date_reported'      => $crisis->date_reported,
                'verified_at'        => $crisis->date_reported->copy()->addDay(),
                'admin_verification' => 1,
                'blockchain_hash'    => hash('sha256', 'CRISIS_VERIFIED_'.$crisis->crisis_id),
            ]);

            // sample donations per case
            for ($j = 0; $j < rand(8, 30); $j++) {
                Donation::create([
                    'crisis_id'       => $crisis->crisis_id,
                    'donor_name'      => 'Donor '.fake()->firstName(),
                    'donor_email'     => fake()->safeEmail(),
                    'donation_amount' => fake()->randomElement([50, 100, 250, 500, 1000]),
                    'donation_date'   => now()->subDays(rand(1, 25)),
                    'payment_method'  => 'FPX',
                    'support_message' => fake()->sentence(),
                ]);
            }
        }

        // a couple of pending crisis reports for admin to review
        for ($i = 0; $i < 3; $i++) {
            $s = Student::skip(5 + $i)->first();
            if (!$s) continue;
            $crisis = Crisis::factory()->create([
                'student_id' => $s->student_id,
                'status'     => 'pending',
            ]);
            CrisisReport::create([
                'student_id'         => $s->student_id,
                'crisis_id'          => $crisis->crisis_id,
                'report_description' => $crisis->crisis_description,
                'report_status'      => 'pending',
                'date_reported'      => now()->subDays(rand(1, 5)),
            ]);
        }
    }
}
