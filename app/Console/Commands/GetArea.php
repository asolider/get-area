<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;

class GetArea extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:getarea';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '3:  获取区/县';

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
         Model\City::chunk(50, function ($citys) {
            $client = new Goutte\Client;

            foreach ($citys as $city) {
                if (empty($city->next_url)) {
                    $this->warn($city->name .' : 当前二级无下级');
                    continue;
                }

                $crawler = $client->request('GET', $city->next_url);

                $crawler->filter('tr.countytr')->each(function ($node) use ($city) {
                    //<td><a href='01/410102.html'>410102000000</a></td><td><a href='01/410102.html'>中原区</a></td>
                    $isoId = $node->filter('td')->first()->text();
                    $name = $node->filter('td')->last()->text();

                    $nextUrl = '';
                    if ($node->filter('a')->count() > 1) {
                        $nextUrl = $this->getNextUrl($city->next_url, $node->filter('a')->first()->attr('href'));
                    }

                    $area = [
                        'iso_id'   => $isoId,
                        'pid'      => $city->id,
                        'name'     => $name,
                        'next_url' => $nextUrl,
                    ];
                    Model\Area::create($area);
                });
            }
        });

        $this->info('over');
    }
}
