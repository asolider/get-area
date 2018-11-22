<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;

class RepairTown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:repairtown {--area=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修补缺失的 4级乡镇数据';

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
        $areaId = $this->option('area');
        $area = Model\Area::find($areaId);
        if (empty($area)) {
            $this->error("$areaId 该区县不存在");
            return;
        }
        if (empty($area->next_url)) {
            $this->error($area->name .' : 当前3级无下级');
            return;
        }
        if (Model\Area::getChildrenCount($area->id) > 0) {
            $this->error($area->name .' : 已经存在 4级区县数据');
            return;
        }
        $this->info("[$area->name] 开始修复 4级数据");
        $client = new Goutte\Client;

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
        $this->info("[$area->name] 下的 4级数据修复完毕");
    }
}
