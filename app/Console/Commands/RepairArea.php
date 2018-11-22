<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;


class RepairArea extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:repairarea {--city=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修补缺失的 3级区县数据';

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
        $cityId = $this->option('city');
        $city = Model\City::find($cityId);
        if (empty($city)) {
            $this->error("$cityId 该城市不存在");
            return;
        }
        if (empty($city->next_url)) {
            $this->error($city->name .' : 当前二级无下级');
            return;
        }
        if (Model\City::getChildrenCount($city->id) > 0) {
            $this->error($city->name .' : 已经存在 3级区县数据');
            return;
        }
        $this->info("[$city->name] 开始修复 3级数据");
        $client = new Goutte\Client;
        $crawler = $client->request('GET', $city->next_url);

        $crawler->filter('tr.countytr,tr.towntr')->each(function ($node) use ($city) {
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

        $this->info("[$city->name] 下的 3级数据修复完毕");
    }

}
