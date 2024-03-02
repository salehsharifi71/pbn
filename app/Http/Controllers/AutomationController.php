<?php

namespace App\Http\Controllers;

use App\Models\Autopost;
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
            if($this->LookupDomain($domain)){
                $domain->status=1;
                $domain->save();

            }else{
                $domain->touch();
            }
        }
    }
    public function QueryIrWhois($domain){

        $data = @dns_get_record($domain, DNS_NS);
        if (is_array($data) && !empty($data)) {
            return true;
        }else{
            $url = "https://whois.nic.ir/WHOIS?name=".$domain;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

            curl_setopt($curl, CURLOPT_USERAGENT, $config['useragent']);
            curl_setopt($curl, CURLOPT_REFERER, 'https://whois.nic.ir/');

            $resp = curl_exec($curl);

            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            // API ERROR
            if ($status === 409) {
                // 409	Concurrent requests limit exceeded. Please, try again or upgrade to the paid plan
                // throw new \Exception('API: ' . $resp);
                die;
            } else if ($status === 404) {
                // 404	The requested URL is not reachable. Please, check it in your browser or try again.
                die;
            } else if ($status === 403) {
                // 403	The API token is wrong or you have exceeded the API credits limit.
                throw new \Exception('API: ' . $resp);
            }


            $regex = '#<\s*?pre\b[^>]*>(.*?)</pre\b[^>]*>#s';
            preg_match_all($regex,$resp,$matches);
            print_r($matches[0][0]);
            curl_close($curl);

            if ($matches[0][0]){
                if (strpos($matches[0][0], 'no entries found') !== false) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }
    public function QueryWhoisServer($whoisserver, $domain) {
        $port = 43;$fp = @fsockopen($whoisserver, $port);
        fputs($fp, $domain . "\r\n");
        $out = "";
        while(!feof($fp)){
            $out .= fgets($fp);
        }
        fclose($fp);

        $res = "";
        if((strpos(strtolower($out), "error") === FALSE) && (strpos(strtolower($out), "not allocated") === FALSE)) {
            $rows = explode("\n", $out);
            foreach($rows as $row) {
                $row = trim($row);
                if(($row != '') && ($row[0] != '#') && ($row[0] != '%')) {
                    $res .= $row."\n";
                }
            }
        }
        return $res;
    }
    public function LookupDomain($domain){
        $whoisservers = array(
            "ac" => "whois.nic.ac", // Ascension Island
            // ad - Andorra - no whois server assigned
            "ae" => "whois.nic.ae", // United Arab Emirates
            "aero"=>"whois.aero",
            "af" => "whois.nic.af", // Afghanistan
            "ag" => "whois.nic.ag", // Antigua And Barbuda
            "ai" => "whois.ai", // Anguilla
            "al" => "whois.ripe.net", // Albania
            "am" => "whois.amnic.net",  // Armenia
            // an - Netherlands Antilles - no whois server assigned
            // ao - Angola - no whois server assigned
            // aq - Antarctica (New Zealand) - no whois server assigned
            // ar - Argentina - no whois server assigned
            "arpa" => "whois.iana.org",
            "as" => "whois.nic.as", // American Samoa
            "asia" => "whois.nic.asia",
            "at" => "whois.nic.at", // Austria
            "au" => "whois.aunic.net", // Australia
            // aw - Aruba - no whois server assigned
            "ax" => "whois.ax", // Aland Islands
            "az" => "whois.ripe.net", // Azerbaijan
            // ba - Bosnia And Herzegovina - no whois server assigned
            // bb - Barbados - no whois server assigned
            // bd - Bangladesh - no whois server assigned
            "be" => "whois.dns.be", // Belgium
            "bg" => "whois.register.bg", // Bulgaria
            "bi" => "whois.nic.bi", // Burundi
            "biz" => "whois.biz",
            "bj" => "whois.nic.bj", // Benin
            // bm - Bermuda - no whois server assigned
            "bn" => "whois.bn", // Brunei Darussalam
            "bo" => "whois.nic.bo", // Bolivia
            "br" => "whois.registro.br", // Brazil
            "bt" => "whois.netnames.net", // Bhutan
            // bv - Bouvet Island (Norway) - no whois server assigned
            // bw - Botswana - no whois server assigned
            "by" => "whois.cctld.by", // Belarus
            "bz" => "whois.belizenic.bz", // Belize
            "ca" => "whois.cira.ca", // Canada
            "cat" => "whois.cat", // Spain
            "cc" => "whois.nic.cc", // Cocos (Keeling) Islands
            "cd" => "whois.nic.cd", // Congo, The Democratic Republic Of The
            // cf - Central African Republic - no whois server assigned
            "ch" => "whois.nic.ch", // Switzerland
            "ci" => "whois.nic.ci", // Cote d'Ivoire
            "ck" => "whois.nic.ck", // Cook Islands
            "cl" => "whois.nic.cl", // Chile
            // cm - Cameroon - no whois server assigned
            "cn" => "whois.cnnic.net.cn", // China
            "co" => "whois.nic.co", // Colombia
            "com" => "whois.verisign-grs.com",
            "coop" => "whois.nic.coop",
            // cr - Costa Rica - no whois server assigned
            // cu - Cuba - no whois server assigned
            // cv - Cape Verde - no whois server assigned
            // cw - Curacao - no whois server assigned
            "cx" => "whois.nic.cx", // Christmas Island
            // cy - Cyprus - no whois server assigned
            "cz" => "whois.nic.cz", // Czech Republic
            "de" => "whois.denic.de", // Germany
            // dj - Djibouti - no whois server assigned
            "dk" => "whois.dk-hostmaster.dk", // Denmark
            "dm" => "whois.nic.dm", // Dominica
            // do - Dominican Republic - no whois server assigned
            "dz" => "whois.nic.dz", // Algeria
            "ec" => "whois.nic.ec", // Ecuador
            "edu" => "whois.educause.edu",
            "ee" => "whois.eenet.ee", // Estonia
            "eg" => "whois.ripe.net", // Egypt
            // er - Eritrea - no whois server assigned
            "es" => "whois.nic.es", // Spain
            // et - Ethiopia - no whois server assigned
            "eu" => "whois.eu",
            "fi" => "whois.ficora.fi", // Finland
            // fj - Fiji - no whois server assigned
            // fk - Falkland Islands - no whois server assigned
            // fm - Micronesia, Federated States Of - no whois server assigned
            "fo" => "whois.nic.fo", // Faroe Islands
            "fr" => "whois.nic.fr", // France
            // ga - Gabon - no whois server assigned
            "gd" => "whois.nic.gd", // Grenada
            // ge - Georgia - no whois server assigned
            // gf - French Guiana - no whois server assigned
            "gg" => "whois.gg", // Guernsey
            // gh - Ghana - no whois server assigned
            "gi" => "whois2.afilias-grs.net", // Gibraltar
            "gl" => "whois.nic.gl", // Greenland (Denmark)
            // gm - Gambia - no whois server assigned
            // gn - Guinea - no whois server assigned
            "gov" => "whois.nic.gov",
            // gr - Greece - no whois server assigned
            // gt - Guatemala - no whois server assigned
            "gs" => "whois.nic.gs", // South Georgia And The South Sandwich Islands
            // gu - Guam - no whois server assigned
            // gw - Guinea-bissau - no whois server assigned
            "gy" => "whois.registry.gy", // Guyana
            "hk" => "whois.hkirc.hk", // Hong Kong
            // hm - Heard and McDonald Islands (Australia) - no whois server assigned
            "hn" => "whois.nic.hn", // Honduras
            "hr" => "whois.dns.hr", // Croatia
            "ht" => "whois.nic.ht", // Haiti
            "hu" => "whois.nic.hu", // Hungary
            // id - Indonesia - no whois server assigned
            "ie" => "whois.domainregistry.ie", // Ireland
            "il" => "whois.isoc.org.il", // Israel
            "im" => "whois.nic.im", // Isle of Man
            "in" => "whois.inregistry.net", // India
            "info" => "whois.afilias.net",
            "int" => "whois.iana.org",
            "io" => "whois.nic.io", // British Indian Ocean Territory
            "iq" => "whois.cmc.iq", // Iraq
            "ir" => "whois.nic.ir", // Iran, Islamic Republic Of
            "is" => "whois.isnic.is", // Iceland
            "it" => "whois.nic.it", // Italy
            "je" => "whois.je", // Jersey
            // jm - Jamaica - no whois server assigned
            // jo - Jordan - no whois server assigned
            "jobs" => "jobswhois.verisign-grs.com",
            "jp" => "whois.jprs.jp", // Japan
            "ke" => "whois.kenic.or.ke", // Kenya
            "kg" => "www.domain.kg", // Kyrgyzstan
            // kh - Cambodia - no whois server assigned
            "ki" => "whois.nic.ki", // Kiribati
            // km - Comoros - no whois server assigned
            // kn - Saint Kitts And Nevis - no whois server assigned
            // kp - Korea, Democratic People's Republic Of - no whois server assigned
            "kr" => "whois.kr", // Korea, Republic Of
            // kw - Kuwait - no whois server assigned
            // ky - Cayman Islands - no whois server assigned
            "kz" => "whois.nic.kz", // Kazakhstan
            "la" => "whois.nic.la", // Lao People's Democratic Republic
            // lb - Lebanon - no whois server assigned
            // lc - Saint Lucia - no whois server assigned
            "li" => "whois.nic.li", // Liechtenstein
            // lk - Sri Lanka - no whois server assigned
            "lt" => "whois.domreg.lt", // Lithuania
            "lu" => "whois.dns.lu", // Luxembourg
            "lv" => "whois.nic.lv", // Latvia
            "ly" => "whois.nic.ly", // Libya
            "ma" => "whois.iam.net.ma", // Morocco
            // mc - Monaco - no whois server assigned
            "md" => "whois.nic.md", // Moldova
            "me" => "whois.nic.me", // Montenegro
            "mg" => "whois.nic.mg", // Madagascar
            // mh - Marshall Islands - no whois server assigned
            "mil" => "whois.nic.mil",
            // mk - Macedonia, The Former Yugoslav Republic Of - no whois server assigned
            "ml" => "whois.dot.ml", // Mali
            // mm - Myanmar - no whois server assigned
            "mn" => "whois.nic.mn", // Mongolia
            "mo" => "whois.monic.mo", // Macao
            "mobi" => "whois.dotmobiregistry.net",
            "mp" => "whois.nic.mp", // Northern Mariana Islands
            // mq - Martinique (France) - no whois server assigned
            // mr - Mauritania - no whois server assigned
            "ms" => "whois.nic.ms", // Montserrat
            // mt - Malta - no whois server assigned
            "mu" => "whois.nic.mu", // Mauritius
            "museum" => "whois.museum",
            // mv - Maldives - no whois server assigned
            // mw - Malawi - no whois server assigned
            "mx" => "whois.mx", // Mexico
            "my" => "whois.domainregistry.my", // Malaysia
            // mz - Mozambique - no whois server assigned
            "na" => "whois.na-nic.com.na", // Namibia
            "name" => "whois.nic.name",
            "nc" => "whois.nc", // New Caledonia
            // ne - Niger - no whois server assigned
            "net" => "whois.verisign-grs.net",
            "nf" => "whois.nic.nf", // Norfolk Island
            "ng" => "whois.nic.net.ng", // Nigeria
            // ni - Nicaragua - no whois server assigned
            "nl" => "whois.domain-registry.nl", // Netherlands
            "no" => "whois.norid.no", // Norway
            // np - Nepal - no whois server assigned
            // nr - Nauru - no whois server assigned
            "nu" => "whois.nic.nu", // Niue
            "nz" => "whois.srs.net.nz", // New Zealand
            "om" => "whois.registry.om", // Oman
            "org" => "whois.pir.org",
            // pa - Panama - no whois server assigned
            "pe" => "kero.yachay.pe", // Peru
            "pf" => "whois.registry.pf", // French Polynesia
            // pg - Papua New Guinea - no whois server assigned
            // ph - Philippines - no whois server assigned
            // pk - Pakistan - no whois server assigned
            "pl" => "whois.dns.pl", // Poland
            "pm" => "whois.nic.pm", // Saint Pierre and Miquelon (France)
            // pn - Pitcairn (New Zealand) - no whois server assigned
            "post" => "whois.dotpostregistry.net",
            "pr" => "whois.nic.pr", // Puerto Rico
            "pro" => "whois.dotproregistry.net",
            // ps - Palestine, State of - no whois server assigned
            "pt" => "whois.dns.pt", // Portugal
            "pw" => "whois.nic.pw", // Palau
            // py - Paraguay - no whois server assigned
            "qa" => "whois.registry.qa", // Qatar
            "re" => "whois.nic.re", // Reunion (France)
            "ro" => "whois.rotld.ro", // Romania
            "rs" => "whois.rnids.rs", // Serbia
            "ru" => "whois.tcinet.ru", // Russian Federation
            // rw - Rwanda - no whois server assigned
            "sa" => "whois.nic.net.sa", // Saudi Arabia
            "sb" => "whois.nic.net.sb", // Solomon Islands
            "sc" => "whois2.afilias-grs.net", // Seychelles
            // sd - Sudan - no whois server assigned
            "se" => "whois.iis.se", // Sweden
            "sg" => "whois.sgnic.sg", // Singapore
            "sh" => "whois.nic.sh", // Saint Helena
            "si" => "whois.arnes.si", // Slovenia
            "sk" => "whois.sk-nic.sk", // Slovakia
            // sl - Sierra Leone - no whois server assigned
            "sm" => "whois.nic.sm", // San Marino
            "sn" => "whois.nic.sn", // Senegal
            "so" => "whois.nic.so", // Somalia
            // sr - Suriname - no whois server assigned
            "st" => "whois.nic.st", // Sao Tome And Principe
            "su" => "whois.tcinet.ru", // Russian Federation
            // sv - El Salvador - no whois server assigned
            "sx" => "whois.sx", // Sint Maarten (dutch Part)
            "sy" => "whois.tld.sy", // Syrian Arab Republic
            // sz - Swaziland - no whois server assigned
            "tc" => "whois.meridiantld.net", // Turks And Caicos Islands
            // td - Chad - no whois server assigned
            "tel" => "whois.nic.tel",
            "tf" => "whois.nic.tf", // French Southern Territories
            // tg - Togo - no whois server assigned
            "th" => "whois.thnic.co.th", // Thailand
            "tj" => "whois.nic.tj", // Tajikistan
            "tk" => "whois.dot.tk", // Tokelau
            "tl" => "whois.nic.tl", // Timor-leste
            "tm" => "whois.nic.tm", // Turkmenistan
            "tn" => "whois.ati.tn", // Tunisia
            "to" => "whois.tonic.to", // Tonga
            "tp" => "whois.nic.tl", // Timor-leste
            "tr" => "whois.nic.tr", // Turkey
            "travel" => "whois.nic.travel",
            // tt - Trinidad And Tobago - no whois server assigned
            "tv" => "tvwhois.verisign-grs.com", // Tuvalu
            "tw" => "whois.twnic.net.tw", // Taiwan
            "tz" => "whois.tznic.or.tz", // Tanzania, United Republic Of
            "ua" => "whois.ua", // Ukraine
            "ug" => "whois.co.ug", // Uganda
            "uk" => "whois.nic.uk", // United Kingdom
            "us" => "whois.nic.us", // United States
            "uy" => "whois.nic.org.uy", // Uruguay
            "uz" => "whois.cctld.uz", // Uzbekistan
            // va - Holy See (vatican City State) - no whois server assigned
            "vc" => "whois2.afilias-grs.net", // Saint Vincent And The Grenadines
            "ve" => "whois.nic.ve", // Venezuela
            "vg" => "whois.adamsnames.tc", // Virgin Islands, British
            // vi - Virgin Islands, US - no whois server assigned
            // vn - Viet Nam - no whois server assigned
            // vu - Vanuatu - no whois server assigned
            "wf" => "whois.nic.wf", // Wallis and Futuna
            "ws" => "whois.website.ws", // Samoa
            "xxx" => "whois.nic.xxx",
            // ye - Yemen - no whois server assigned
            "yt" => "whois.nic.yt", // Mayotte
            "yu" => "whois.ripe.net");
        $domain_parts = explode(".", $domain);
        $tld = strtolower(array_pop($domain_parts));
        $whoisserver = $whoisservers[$tld];
        if($tld!='ir') {
            if (!$whoisserver) {
                return false;
            }
            $result = $this->QueryWhoisServer($whoisserver, $domain);

            if (!$result) {
                return false;
            } else {
                while (strpos($result, "Whois Server:") !== FALSE) {
                    preg_match("/Whois Server: (.*)/", $result, $matches);
                    $secondary = $matches[1];
                    if ($secondary) {
                        $result = $this->QueryWhoisServer($secondary, $domain);

                        if((strpos(strtolower($result), "error") === FALSE) && (strpos(strtolower($result), "not allocated") === FALSE) && (strpos(strtolower($result), "no match for") === FALSE) && (strpos(strtolower($result), "no found") === FALSE) && (strpos(strtolower($result), "not found") === FALSE) && (strpos(strtolower($result), "no data found") === FALSE)) {
                            return false;
                        }else{
                            return true;
                        }
                    }
                }
            }
            return false;
        }else{
            //.ir domain
            return $this->QueryIrWhois($domain);
        }
    }
    public function autoPost($id){
        $post=Autopost::where('status',0)->where('source_id',$id)->firstOrFail();
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
        return $html;
        $post->nonhtml=strip_tags($html);
        return $post;
    }
}
