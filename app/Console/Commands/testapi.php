<?php

namespace App\Console\Commands;

use App\Models\UiaStaff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Symfony\Component\DomCrawler\Crawler;


class testapi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $baseUrl = 'https://api.quddus.my/api';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //

        $data = $this->login("2225498", "jOB1Te_H7");
        $getProfile = $this->getProfile($data);

        dd($getProfile);
        //$getSchedule = $this->getSchedule($data);

        // $getListStaff = $this->getListStaff();


        // dd($getSchedule);
        // dd($getProfile);
        // dd($getListStaff);

        (new User())->playwrightscrapelogin("2225498", "jOB1Te_H7");
        // $this->playwrightscrapelogin();
    }


    function playwrightscrapelogin()
    {
        $username = "2225498";
        $password = "jOB1Te_H7";
        $scriptPath = resource_path('scripts/crawl-login.js');

        // Pass variables as arguments after the script path
        $result = Process::run("node \"$scriptPath\" $username $password");


        $output = $result->output();
        $userData = json_decode($output, true);
        dd($userData);
    }

    public function login($username, $password)
    {
        $response = Http::post("{$this->baseUrl}/auth/login", [
            'username' => $username,
            'password' => $password,
        ]);

        if ($response->successful()) {
            return $response->json()['data']['token'];
        }

        throw new \Exception($response->json()['message'] ?? 'Login failed');
    }

    /**
     * Step 2: Fetch Profile using the token
     */
    public function getProfile($token)
    {
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/profile");

        return $response->json();
    }

    /**
     * Fetch Schedule
     */
    public function getSchedule($token)
    {
        return Http::withToken($token)
            ->get("{$this->baseUrl}/schedule")
            ->json();
    }


    public function getListStaff()
    {
        $baseUrl = 'https://www.iium.edu.my/directory/';

        // 1. First, fetch the main page to get all KCDIOM department names/IDs
        $mainPage = Http::get($baseUrl);
        $mainCrawler = new Crawler($mainPage->body());

        $departments = [];
        $mainCrawler->filter('select[name="kcdiom"] option')->each(function (Crawler $node) use (&$departments) {
            $id = $node->attr('value');
            $name = trim($node->text());

            // Skip the "ALL KCDIOM" option
            if (!empty($id)) {
                $departments[$id] = $name;
            }
        });

        $this->info("Found " . count($departments) . " departments. Starting crawl...");

        $allStaff = [];

        // 2. Loop through each Department
        foreach ($departments as $id => $deptName) {
            $page = 1;
            $this->warn("\nScraping Department: $deptName (ID: $id)");

            while (true) {
                $this->line("   Page $page...");

                $response = Http::get($baseUrl, [
                    'kcdiom' => $id,
                    'page'   => $page,
                    'sort'   => '01'
                ]);

                if (!$response->successful()) break;

                $crawler = new Crawler($response->body());
                $items = $crawler->filter('.card-body');

                if ($items->count() === 0) break;

                $newItemsFound = 0;
                $items->each(function (Crawler $node) use (&$allStaff, $deptName, &$newItemsFound, $id) {
                    $name = $node->filter('h5.card-title')->count() > 0
                        ? trim($node->filter('h5.card-title')->text())
                        : null;

                    if (!$name) return;

                    $emailNode = $node->filter('.col-md-10');
                    $email = $emailNode->count() > 0 ? trim($emailNode->first()->text()) : 'N/A';

                    $allStaff[] = [
                        'name'       => $name,
                        'email'      => $email,
                        'department' => $deptName // Adding the info you wanted
                    ];
                    $newItemsFound++;


                    // ... inside the scraper each loop ...
                    $staffData = [
                        'name'       => $name,
                        'email'      => $email,
                        'department' => $deptName,
                        'kcdiom_id'  => $id,
                    ];

                    // Use email as the unique identifier to avoid duplicates
                    UiaStaff::updateOrCreate(
                        ['email' => $email, 'name' => $name], // Search criteria
                        $staffData                           // Data to update/insert
                    );
                });

                if ($newItemsFound === 0) break;

                $page++;
                sleep(1); // Politeness delay

                // Limit pages per department for testing if needed
                if ($page > 10) break;
            }
        }

        // 3. Display the result
        $this->table(['Name', 'Email', 'Department'], $allStaff);
        $this->info("Total staff scraped: " . count($allStaff));
    }


    public function getListStaff2()
    {
        $url = 'https://www.iium.edu.my/directory/?letter=&name=&expertise=&kcdiom=237&type=&sort=01';

        // 1. Fetch the HTML content
        $response = Http::get($url);
        $html = $response->body();

        // 2. Load it into the Crawler
        $crawler = new Crawler($html);

        // 3. Filter the staff containers
        // Based on the directory structure, each staff entry is often in a specific div or row
        $staffData = $crawler->filter('.staff-list-item, .directory-item')->each(function (Crawler $node) {

            // Extract Name (Adjust selector based on exact class/tag)
            $name = $node->filter('h4, .staff-name')->count() > 0
                ? trim($node->filter('h4, .staff-name')->text())
                : 'N/A';

            // Extract Email (Looks for mailto links)
            $email = $node->filter('a[href^="mailto:"]')->count() > 0
                ? str_replace('mailto:', '', $node->filter('a[href^="mailto:"]')->attr('href'))
                : 'N/A';

            return [
                'name'  => $name,
                'email' => $email,
            ];
        });

        return response()->json($staffData);
    }
}
