<?php
/**
 * Created by PhpStorm.
 * User: kobe.wang
 * Date: 2021/5/7
 * Time: 19:15
 */
return [
    'client_id' => env('UAM_AUTH_CLIENT_ID','oauth-center'),
    'client_secret' => env('UAM_AUTH_CLIENT_SECRET','57d25fc6-9b43-4eca-8a0c-44a31facf8bb'),
    'grant_type' => env('UAM_AUTH_GRANT_TYPE','client_credentials'),
    'uam_auth_api' => env('UAM_AUTH_API','http://web-keycloak-sit.etocrm.net'),//获取token地址
    'uam_api' => env('UAM_API','https://apiserver-saas-sit.woaap.com'), //后台调用uam接口地址
];

