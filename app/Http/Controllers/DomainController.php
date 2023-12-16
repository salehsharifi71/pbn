<?php

namespace App\Http\Controllers;

use App\Models\Domains;
use App\Models\Pages;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    //
    public function index($lvl1=false,$lvl2=false,$lvl3=false,$lvl4=false){
        $domain=Domains::where('domain', request()->server->get('HTTP_HOST'))->firstOrFail();
        $page=Pages::where('domain_id',$domain->id)->where('slug',$_SERVER['PATH_INFO'])->first();
        if(!$page){
            $page=Pages::where('domain_id',$domain->id)->where('slug','allBySalehDefault')->firstOrFail();
        }
        if($page->is_redirect)
            return redirect($page->data,301);
        else
            return view('pages.'.$page->data,compact('page','domain'));
    }
}
