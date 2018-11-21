<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;

class GetCity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:getcity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '2: 获取市';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getNextUrl($currentUrl, $shortNextUrl)
    {
        return preg_replace('/\d{2}\.html/', $shortNextUrl, $currentUrl);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $privinceList = Model\Province::all();
        $client = new Goutte\Client;



        foreach ($privinceList as $key => $province) {
            $crawler = $client->request('GET', $province->next_url);

            $crawler->filter('tr.citytr')->each(function ($node) use ($province) {
                //<td><a href='41/4101.html'>410100000000</a></td><td><a href='41/4101.html'>郑州市</a></td>
                $isoId = $node->filter('td')->first()->text();
                $name = $node->filter('td')->last()->text();

                $nextUrl = '';
                if ($node->filter('a')->count() > 1) {
                    $nextUrl = $this->getNextUrl($province->next_url, $node->filter('a')->first()->attr('href'));
                }

                $city = [
                    'iso_id'   => $isoId,
                    'pid'      => $province->id,
                    'name'     => $name,
                    'next_url' => $nextUrl,
                ];
                Model\City::create($city);
            });
        }
        $this->info('over');
    }
}
