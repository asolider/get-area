<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

class Check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查数据是否完整';

    protected $checkLevel = 1;

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
        $this->checkLevel = $this->ask('选择检查深度? [1,2,3,4]', 1);

        $this->ProvincesToCity();
    }

    // 检查1->2
    private function ProvincesToCity()
    {
        Model\Province::chunk(100, function ($provinces) {
            foreach ($provinces as $province) {
                if (empty(Model\Province::getChildrenCount($province->id))) {
                    $this->warn("[{$province->name}($province->id)] : 无二级市");
                    continue;
                }
                if ($this->checkLevel <= 1) {
                    continue;
                }

                Model\City::where('pid', $province->id)->chunk(100, function ($citys) use ($province) {
                    foreach ($citys as $city) {
                        if (empty(Model\City::getChildrenCount($city->id))) {
                            $this->warn("[{$province->name}($province->id)->{$city->name}($city->id)] : 无 3级区县");

                            $this->call('location:repairarea', ['--city' => $city->id]);

                            continue;
                        }
                        if ($this->checkLevel <= 2) {
                            continue;
                        }

                        Model\Area::where('pid', $city->id)->chunk(100, function ($areas) use ($province, $city) {
                            foreach ($areas as $area) {
                                if (empty(Model\Area::getChildrenCount($area->id))) {
                                    $this->warn("[{$province->name}($province->id)->{$city->name}($city->id)->{$area->name}($area->id)] : 无 4级乡镇");

                                    $this->call('location:repairtown', ['--area' => $area->id]);
                                    continue;
                                }
                            }
                        });
                    }
                });

            }
        });
    }

}
