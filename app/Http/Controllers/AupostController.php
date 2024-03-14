<?php

namespace App\Http\Controllers;

use App\Models\AuPostMetas;
use App\Models\AuPostQues;
use App\Models\AuPostSource;
use App\Models\AuPostTargets;
use App\Services\StringService;
use Carbon\Carbon;
use DonatelloZa\RakePlus\RakePlus;
use Illuminate\Http\Request;

class AupostController extends Controller
{
    //
    public function getContentByCron(){

        $source=AuPostSource::where('is_active',1)->where('updated_at','<',Carbon::now()->subMinutes(60)->toDateTimeString())->firstOrFail();
        $source->touch();
        echo $source->id.' :<br>';
        if($source->kind==1){
            $url=$source->webservice;
            $articles=@file_get_contents($url);
            $articles=json_decode($articles,true);
            foreach($articles as $article){
                $link=$article['link'];
                if(!AuPostQues::where('url',$link)->first()){
                    $title = $article['title']['rendered'];
                    $description= $article['content']['rendered'];
                    if($source->id==1){
                        $stringService=new StringService();
                        $html=$description;
                        $toc=$stringService->getStringBetween('<div id="ez-toc-container"','</nav></div>',$html);
                        $html=str_replace('<div id="ez-toc-container"'.$toc.'</nav></div>','',$html);

                        $ads=$stringService->getStringBetween('<div class="firstOfContent">','</div>',$html);
                        $html=str_replace('<div class="firstOfContent">'.$ads.'</div>','',$html);
                        $ads=$stringService->getStringBetween('<div class="endOfContent">','</div>',$html);
                        $html=str_replace('<div class="endOfContent">'.$ads.'</div>','',$html);
                        $ads=$stringService->getStringBetween('<div class="sticky-down">','</div>',$html);
                        $html=str_replace('<div class="sticky-down">'.$ads.'</div>','',$html);
                        $description=str_replace('\n' ,'
',$html);
                    }
                    $img='';
                    $short=false;
                    if(isset($article['yoast_head_json']['og_image'][0]['url']))
                        $img= $article['yoast_head_json']['og_image'][0]['url'];
                    if(isset($article['yoast_head_json']['description']))
                        $short= $article['yoast_head_json']['description'];
                    $this->sendImmediately($source,$link,$img,$title,$description,$short);
                    $targets=explode(',',$source->available_targets);
                    foreach ($targets as $target){
//                        echo $target.',';
                        $rand=rand(0,100);
                        if($source->share_rate<$rand){
                            $postQue=new AuPostQues();
                            $postQue->source_id=$source->id;
                            $postQue->target=$target;
                            $postQue->url=$link;
                            $postQue->img=$img;
                            $postQue->title=$title;
                            $postQue->content=$description;
                            $postQue->save();
                        }
                    }

                }
            }
        }
    }
    public function sendImmediately($source,$link,$img,$title,$description,$short){
        //is telegram token available
        if($token=AuPostMetas::where('kind','source')->where('ap_id',$source->id)->where('meta_key','tg_bot')->first()){
            $token=$token->meta_value;
            //get telegram channel
            if($chat_id=AuPostMetas::where('kind','source')->where('ap_id',$source->id)->where('meta_key','chat_id')->first()){
                $chat_id=$chat_id->meta_value;
                $caption= urlencode('<a href="'.$link.'">'.$title.'</a>
'.$short);
                try {
                    echo file_get_contents('https://api.telegram.org/bot'.$token.'/sendPhoto?caption=' . $caption . '&chat_id=' . $chat_id . '&parse_mode=HTML&photo=' . $img);
                }catch (\Exception $exception){
//                    echo $exception->getMessage();
                }
            }

        }
    }
    public function makeKeyword($title,$description){
        $rake = RakePlus::create($title.' '.strip_tags($description), 'fa_IR');
        return  $rake->sortByScore('desc')->get();
    }
    public function getNewPost($id){
        $posts=AuPostQues::where('status',0)->where('source_id',$id)->inRandomOrder()->get();
        foreach ($posts as $post){
            if($target=AuPostTargets::where('slug',$post->target)->where('is_active',1)->where('updated_at','<',Carbon::now()->subDay()->toDateTimeString())->first()){
                if($target->kind==1){
                    $post->nonhtml=strip_tags($post->content);
                    $post->nonhtml=str_replace("\n",'
',$post->nonhtml);
                    $type = pathinfo($post->img, PATHINFO_EXTENSION);
                    $data = @file_get_contents($post->img);
                    $post->base64img='data:image/' . $type . ';base64,' . base64_encode($data);
                    $post->link='<a href="'.$post->url.'">'.substr($post->title,0,strpos($post->title,'(')).'</a>';
                    return $post;
                }
            }
        }
        return json_encode([]);


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
    public function test(){
        $posts=AuPostQues::whereNull('keywords')->inRandomOrder()->get();
        foreach ($posts as $post) {
            $phrases = $this->makeKeyword($post->title, $post->content);
            $keywords = [];
            $count = 0;
            foreach ($phrases as $phrase) {
                if ($count < 5 && count(explode(' ', $phrase)) < 4) {
                    array_push($keywords, $phrase);
                    $count++;
                }
            }
            $post->keywords = implode(',', $keywords);

            $post->title = $post->title . ' (' . $keywords[rand(0, count($keywords) - 1)] . ')';
            $post->save();
        }
        return $keywords;
    }
}
