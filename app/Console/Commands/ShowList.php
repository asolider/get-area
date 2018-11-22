<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

class ShowList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:showlist {provinceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '展示省市区数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $provinceId = (int)$this->argument('provinceId');
        $header = ['省', '市', '区'];
        $list = [];
        Model\Area::where(function($query) use ($provinceId) {

        })->with(['city'])->chunk(100, function ($areas) use (& $list, $provinceId) {
            foreach ($areas as $area) {
                if ($provinceId && $provinceId == $area->city->province->id) {
                    $list[] = [
                        'province' => $area->city->province->name,
                        'city'     => $area->city->name,
                        'area'     => $area->name,
                    ];
                }
            }
        });

        $this->table($header, $list);
    }
}
