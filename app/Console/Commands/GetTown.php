<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;

class GetTown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:gettown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '4:  获取乡镇';

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
         Model\Area::chunk(100, function ($areas) {
            $client = new Goutte\Client;

            foreach ($areas as $area) {
                $this->info('乡镇id： ' . $area->id);

                if (empty($area->next_url)) {
                    $this->warn($area->name .' : 当前区县无下级');
                    continue;
                }

                $crawler = $client->request('GET', $area->next_url);

                $crawler->filter('tr.towntr')->each(function ($node) use ($area) {
                    //<td><a href='01/410102.html'>410102000000</a></td><td><a href='01/410102.html'>中原区</a></td>
                    $isoId = $node->filter('td')->first()->text();
                    $name = $node->filter('td')->last()->text();

                    $nextUrl = '';
                    if ($node->filter('a')->count() > 1) {
                        $nextUrl = $this->getNextUrl($area->next_url, $node->filter('a')->first()->attr('href'));
                    }

                    $town = [
                        'iso_id'   => $isoId,
                        'pid'      => $area->id,
                        'name'     => $name,
                        'next_url' => $nextUrl,
                    ];
                    Model\Town::create($town);
                });
            }
        });

        $this->info('over');
    }
}
