<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Model;

use Goutte;

class GetProvince extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:getprovince';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1: 获取省列表';

    private $fromUrl = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2017/index.html';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getNextUrl($next)
    {
        return str_replace('index.html', $next, $this->fromUrl);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Goutte\Client;
        $crawler = $client->request('GET', $this->fromUrl);

        $crawler->filter('a')->each(function ($node) {
            $href = $node->attr('href');

            if (!preg_match('/\d{2}\.html/', $href)) {
                return;
            }

            $nextUrl = $this->getNextUrl($href);

            $province = [
                'iso_id'   => trim($href, '.html'),
                'name'     => $node->text(),
                'next_url' => $nextUrl,
            ];

            Model\Province::create($province);
        });

        $this->info('over');
    }
}
