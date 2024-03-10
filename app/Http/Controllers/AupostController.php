<?php

namespace App\Http\Controllers;

use App\Models\AuPostSource;
use App\Services\StringService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AupostController extends Controller
{
    //
    public function getContentByCron(){
        $source=AuPostSource::where('is_active',1)->where('updated_at','<',Carbon::now()->subMinutes(60))->firstOrFail();
        $source->touch();


    }
    public function autoPost($id){
        $post=Autopost::where('status',0)->where('source_id',$id)->first();
        if(!$post)
            return json_encode([]);
        $post->status=100;
//        $post->save();
        $stringService=new StringService();
        $html=$post->content;
        $toc=$stringService->getStringBetween('<div id="ez-toc-container"','</nav></div>',$html);
        $html=str_replace('<div id="ez-toc-container"'.$toc.'</nav></div>','',$html);

        $ads=$stringService->getStringBetween('<div class="firstOfContent">','</div>',$html);
        $html=str_replace('<div class="firstOfContent">'.$ads.'</div>','',$html);
        $ads=$stringService->getStringBetween('<div class="endOfContent">','</div>',$html);
        $html=str_replace('<div class="endOfContent">'.$ads.'</div>','',$html);
        $ads=$stringService->getStringBetween('<div class="sticky-down">','</div>',$html);
        $html=str_replace('<div class="sticky-down">'.$ads.'</div>','',$html);
        $html=str_replace('\n' ,'
',$html);
        $post->nonhtml=strip_tags($html);
        return $post;
    }
    public function generateHTML(){
        if(\request()->has('title')){
            $html='<img src="'.\request('img').'">
'.\request('content');
            $stringService=new StringService();
            if(strpos($html,\request('title')))
                $html=$stringService->str_replace_first(\request('title'),'<a href="'.\request('title').'">'.\request('title').'</a>',$html);
            else
                $html.='<br>'.'<a href="'.\request('title').'">'.\request('title').'</a>';
            return view('forms.makeHTML',compact('html'));
        }
        return view('forms.makeHTML');
    }
}
