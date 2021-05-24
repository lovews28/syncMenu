<?php

namespace Ws\SyncMenu\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步菜单到uam';

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
        $this->info('-----------sync begin-------------');
        $configs = config('menu');
        dd($configs);
        foreach ($configs as $key => $menus) {
            foreach($menus as $key2 => $menu) {
                if(!DB::table('sync_menu_migrations')->where('migrations',$key.'_'.$key2)->first()) {
                   DB::table('sync_menu_migrations')->insert(
                       [
                           'migrations' => $key.'_'.$key2,
                           'created_at' => date('Y-m-d H:i:s'),
                           'updated_at' => date('Y-m-d H:i:s'),
                       ]
                   );
                   $this->info('----------------sync success '.$key.'_'.$key2.'-------------');
                }
            }
        }
        $this->info('-----------sync finish-------------');
    }
}
