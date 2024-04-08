<?php

namespace App\Http\Controllers;

use App\Models\AuPostMetas;
use App\Models\Checkers;
use App\Services\StringService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    //
    public function chkDomain(){

        $domains=Checkers::where('status',0)->where('updated_at','<',Carbon::now()->subDays(2))->take(2)->get();
        foreach ($domains as $domain){
            $isFree=false;
            if ( checkdnsrr($domain->domain.'.', 'ANY') ) {
                echo "DNS Record found";
            }else {
                $isFree=true;
            }
            if ( gethostbyname($domain->domain) != $domain->domain ) {
                echo "DNS Record found";
            }else {
                $isFree=true;
            }
            if($isFree){
                if($token=AuPostMetas::where('kind','source')->where('ap_id',2)->where('meta_key','tg_bot')->first()) {
                    $token = $token->meta_value;
                    @file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?text=you can register : '.$domain->domain.'&chat_id=123969916');
                }
                $domain->status=1;
                $domain->save();

            }else{

                $domain->touch();
            }
        }
    }


}
