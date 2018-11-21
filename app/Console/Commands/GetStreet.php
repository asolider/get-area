<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;
use DB;

use Goutte;

class GetStreet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:getstreet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '5:  获取行政村';

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
        return preg_replace('/\d+\.html/', $shortNextUrl, $currentUrl);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
         Model\Town::chunk(100, function ($towns) {
            $client = new Goutte\Client;

            foreach ($towns as $town) {
                $this->info('行政村id： ' . $town->id);
                if (empty($town->next_url)) {
                    $this->warn($town->name .' : 当前行政村无下级');
                    continue;
                }

                $crawler = $client->request('GET', $town->next_url);

                $streets = [];

                $crawler->filter('tr.villagetr')->each(function ($node) use ($town, &$streets) {
                    //<td><a href='01/410102.html'>410102000000</a></td><td><a href='01/410102.html'>中原区</a></td>
                    $isoId = $node->filter('td')->first()->text();
                    $name = $node->filter('td')->last()->text();

                    $streets[] = [
                        'iso_id'   => $isoId,
                        'pid'      => $town->id,
                        'name'     => $name,
                        'next_url' => '',
                    ];
                });

                DB::table('street')->insert($streets);
            }
        });
        $this->info('over');
    }
}
