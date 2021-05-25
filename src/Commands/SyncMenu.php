<?php

namespace Ws\SyncMenu\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ws\SyncMenu\Common\Http;

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
        $paths = storage_path('menu-template');
        $dirs = scandir($paths);
        foreach($dirs as $dir){
            if('csv' === pathinfo($dir)['extension'] ){
                if(!DB::table('sync_menu_migrations')->where('migrations',pathinfo($dir)['filename'])->first()) {
                    if($data = $this->readFile(storage_path('menu-template/'.$dir))) {
                        $this->info('-----------sync filename:'.$dir.'----begin---------');
                        $error_line = [];
                        $i = 0;
                        foreach($data as $key => $value) {
                            $i++;
                            $result =  $this->upMenu([
                                'icon' => $value['icon'],
                                'id' => (int)$value['id'],
                                'menuName' => $value['menuName'],
                                'menuRoute' => $value['menuRoute'],
                                'systemId' => (int)$value['systemId'],
                                'level' => (int)$value['level'],
                                'isButton' => (int)$value['isButton'],
                                'status' => (int)$value['status'],
                                'parentId' => (int)$value['parentId'],
                                'isDisplay' => (boolean)$value['isDisplay']
                            ]);
                            if(!$result)
                                $error_line [] = $i;
                        }
                        if(count($data) <= count($error_line))
                            DB::table('sync_menu_migrations')->insert(
                                [
                                    'migrations' => pathinfo($dir)['filename'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]
                            );
                        $lines = implode(',',$error_line);
                        $this->info('-----------sync filename:'.$dir.'----finish-----fail:'.$lines.'----');
                    }
                }
            }
        }
        $this->info('-----------sync finish-------------');
    }

    public function readFile($filepath)
    {
        $data = [];
        if (!file_exists($filepath)) {
            return $data;
        }

        //进行文件读取
        $i = 0;
        if (($file = fopen($filepath, 'r')) !== false) {
            while (($line = fgetcsv($file)) !== false) {
                try {
                    $i++;
                    //进行编码处理
                    foreach ($line as $key => $value) {
                        $line[$key] = mb_convert_encoding(trim($value,''), 'UTF-8', ['GB2312','GBK','UTF-8']);
                    }

                    //以第一行的值为数组的键
                    if ($i <= 1) {
                        $keys = $line;
                        continue;
                    }
                    //格式化数组的键
                    $newLine = [];
                    foreach ($line as $key => $value) {
                        if (isset($keys[$key])) {
                            $newLine[$keys[$key]] = $value;
                        } else {
                            $newLine[$key] = $value;
                        }
                    }

                    $data[] = $newLine;
                } catch (\Exception $exception) {
                    \Illuminate\Support\Facades\Log::alert('读取文件' . $filepath . '失败');
                    continue;
                }
            }
            fclose($file);
        }

        return $data;
    }

    /**
     * 上报菜单
     *
     */
    public function upMenu($params)
    {
        $token = $this->getUpMenuToken();
        $token = "Bearer ".$token['access_token'];
        if(empty($token)){
            return ['errMsg' => 'token有误'];
        }
        $header =[
            'content-type' => 'application/json',
            'Authorization'=>$token
        ];
        $url = config('uamConfig.uam_api').'/uam/permission/update';
        $result = json_decode(Http::post($url,json_encode($params),false,$header),true);
        if($result['code']==0){
            return $result;
        }else{
            Log::info(__METHOD__,['header'=>$header,'url'=>$url,'res'=>$result]);
            return false;
        }
    }

    /**
     * 获取上报菜单token
     *
     */
    public function getUpMenuToken()
    {
        $url = config('uamConfig.uam_auth_api').'/auth/realms/uam/protocol/openid-connect/token';
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $post = [
            'client_id' => config('uamConfig.client_id'),
            'client_secret' => config('uamConfig.client_secret'),
            'grant_type' => config('uamConfig.grant_type')
        ];
        $result = Http::post($url,$post,false,$header);
        $result = json_decode($result,1);
        if($result){
            return $result;
        }else{
            Log::info(__METHOD__,['post'=>$post,'url'=>$url,'res'=>$result]);
            return false;
        }
    }
}
