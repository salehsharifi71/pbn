<?php
/**
 * Created by PhpStorm.
 * User: MrFenj
 * Date: 22/08/2020
 * Time: 11:59 AM
 */

namespace App\Services;

class StringService
{

    public function onlyEnNumber($string){
        $number=$this->convertNumbersToEnglish($string);
//        preg_match("/([0-9]+)/", $number, $matches);
//        preg_match_all('/\d+/', $number, $matches);
        preg_match_all('!\d+!', $number, $matches);
        $end='';
        foreach ($matches[0] as $num)
            $end=$end.$num;
        return $end;
    }
    public function isMobile($mobile){
        if (!$mobile){
            return false;
        }
        $mobile = $this->convertNumbersToEnglish($mobile);
        return preg_match("/^09\d{9}$/",$mobile);
    }

    public function isEmail($email){
        if (!$email){
            return false;
        }
        return preg_match("/^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,4})$/",$email);
    }

    public function validateNationalId($nationalId){
        //return true;
        if (!$nationalId){
            return false;
        }
        $nationalId = $this->convertNumbersToEnglish($nationalId);
        if (!preg_match("/^\d{10}$/",$nationalId)){
            return false;
        }
        $sum = 0;
        for ($i=0;$i<=8;$i++) {
            $sum += (10-$i)*substr($nationalId,$i,1);
        }

        $n = $sum%11;
        if ($n>=2){
            $n = 11-$n;
        }

        if ((int) substr($nationalId,9,1) === $n){
            return true;
        }else{
            return false;
        }
    }

    public function convertNumbersToFarsi($matches){
        $farsi_array = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $arabic_array = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $english_array = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $matches = str_replace($arabic_array, $farsi_array, $matches);
        $matches = str_replace($english_array, $farsi_array, $matches);
        return $matches;
    }

    public function beautyFarsiNumbers($number, $decimals = 0){
        $number = number_format($number,$decimals,'٫','٬');

        return $this->convertNumbersToFarsi($number);
    }

    public function convertNumbersToEnglish($matches){
        $farsi_array = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $arabic_array = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
        $english_array = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

        $matches = str_replace($arabic_array, $english_array, $matches);
        $matches = str_replace($farsi_array, $english_array, $matches);
        return $matches;
    }

    public function convertCharsToFarsi($matches, $convertNumbers = true){
        if ($convertNumbers) {
            $arabic_array = array('ك', 'ي', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
            $farsi_array  = array('ک', 'ی', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        }else{
            $arabic_array = array('ك', 'ي');
            $farsi_array  = array('ک', 'ی');
        }

        return str_replace($arabic_array, $farsi_array, $matches);
    }

    public function hideEmail($email=''){
        if (!$email){
            return '';
        }
        if (strpos($email,'@')===false){
            return $email;
        }
        $email_ = explode('@',$email);
        if (strlen($email_[0])<6){
            $hiddenMail = substr($email_[0],0,1) . '...' . substr($email_[0],strlen($email_[0])-1,1) . '@' . $email_[1];
        }else{
            $hiddenMail = substr($email_[0],0,2) . '...' . substr($email_[0],strlen($email_[0])-2,2) . '@' . $email_[1];
        }
        return $hiddenMail;
    }

    function getStringBetween( $from,  $to,  $haystack)
    {
        try {
//        if(!strpos($haystack, $from))
//            return '';
            $fromPosition = strpos($haystack, $from) + strlen($from);
//        if(!strpos($haystack, $to, $fromPosition))
//            return '';
            $toPosition = strpos($haystack, $to, $fromPosition);
            $betweenLength = $toPosition - $fromPosition;
            return substr($haystack, $fromPosition, $betweenLength);
        }catch (\Exception $e){
            return false;
        }
    }
    public function hideMobile($mobile=''){
        if (!$mobile){
            return '';
        }
        if (strlen($mobile)==11){
            $hiddenMobile = substr($mobile,0,6) . 'xxx' . substr($mobile,9,2);
        }else{
            $hiddenMobile = substr($mobile,0,5) . 'xxx' . substr($mobile,8,2);
        }
        return $hiddenMobile;
    }

    public function getWords($sentence, $count = 10) {
        preg_match("/(?:[^\s,\.;\?\!]+(?:[\s,\.;\?\!]+|$)){0,$count}/", $sentence, $matches);
        return $matches[0];
    }

    private function reset_mbstring_encoding() {
        $this->mbstring_binary_safe_encoding( true );
    }

    private function mbstring_binary_safe_encoding( $reset = false ) {
        static $encodings  = array();
        static $overloaded = null;

        if ( is_null( $overloaded ) ) {
            $overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );
        }

        if ( false === $overloaded ) {
            return;
        }

        if ( ! $reset ) {
            $encoding = mb_internal_encoding();
            array_push( $encodings, $encoding );
            mb_internal_encoding( 'ISO-8859-1' );
        }

        if ( $reset && $encodings ) {
            $encoding = array_pop( $encodings );
            mb_internal_encoding( $encoding );
        }
    }

    private function seems_utf8( $str ) {
        $this->mbstring_binary_safe_encoding();
        $length = strlen($str);
        $this->reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
            else return false; // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    private function utf8_uri_encode( $utf8_string, $length = 0 ) {
        $unicode = '';
        $values = array();
        $num_octets = 1;
        $unicode_length = 0;

        $this->mbstring_binary_safe_encoding();
        $string_length = strlen( $utf8_string );
        $this->reset_mbstring_encoding();

        for ($i = 0; $i < $string_length; $i++ ) {

            $value = ord( $utf8_string[ $i ] );

            if ( $value < 128 ) {
                if ( $length && ( $unicode_length >= $length ) )
                    break;
                $unicode .= chr($value);
                $unicode_length++;
            } else {
                if ( count( $values ) == 0 ) {
                    if ( $value < 224 ) {
                        $num_octets = 2;
                    } elseif ( $value < 240 ) {
                        $num_octets = 3;
                    } else {
                        $num_octets = 4;
                    }
                }

                $values[] = $value;

                if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
                    break;
                if ( count( $values ) == $num_octets ) {
                    for ( $j = 0; $j < $num_octets; $j++ ) {
                        $unicode .= '%' . dechex( $values[ $j ] );
                    }

                    $unicode_length += $num_octets * 3;

                    $values = array();
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }

    private function remove_accents( $string ) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if ($this->seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                'ª' => 'a', 'º' => 'o',
                'À' => 'A', 'Á' => 'A',
                'Â' => 'A', 'Ã' => 'A',
                'Ä' => 'A', 'Å' => 'A',
                'Æ' => 'AE','Ç' => 'C',
                'È' => 'E', 'É' => 'E',
                'Ê' => 'E', 'Ë' => 'E',
                'Ì' => 'I', 'Í' => 'I',
                'Î' => 'I', 'Ï' => 'I',
                'Ð' => 'D', 'Ñ' => 'N',
                'Ò' => 'O', 'Ó' => 'O',
                'Ô' => 'O', 'Õ' => 'O',
                'Ö' => 'O', 'Ù' => 'U',
                'Ú' => 'U', 'Û' => 'U',
                'Ü' => 'U', 'Ý' => 'Y',
                'Þ' => 'TH','ß' => 's',
                'à' => 'a', 'á' => 'a',
                'â' => 'a', 'ã' => 'a',
                'ä' => 'a', 'å' => 'a',
                'æ' => 'ae','ç' => 'c',
                'è' => 'e', 'é' => 'e',
                'ê' => 'e', 'ë' => 'e',
                'ì' => 'i', 'í' => 'i',
                'î' => 'i', 'ï' => 'i',
                'ð' => 'd', 'ñ' => 'n',
                'ò' => 'o', 'ó' => 'o',
                'ô' => 'o', 'õ' => 'o',
                'ö' => 'o', 'ø' => 'o',
                'ù' => 'u', 'ú' => 'u',
                'û' => 'u', 'ü' => 'u',
                'ý' => 'y', 'þ' => 'th',
                'ÿ' => 'y', 'Ø' => 'O',
                // Decompositions for Latin Extended-A
                'Ā' => 'A', 'ā' => 'a',
                'Ă' => 'A', 'ă' => 'a',
                'Ą' => 'A', 'ą' => 'a',
                'Ć' => 'C', 'ć' => 'c',
                'Ĉ' => 'C', 'ĉ' => 'c',
                'Ċ' => 'C', 'ċ' => 'c',
                'Č' => 'C', 'č' => 'c',
                'Ď' => 'D', 'ď' => 'd',
                'Đ' => 'D', 'đ' => 'd',
                'Ē' => 'E', 'ē' => 'e',
                'Ĕ' => 'E', 'ĕ' => 'e',
                'Ė' => 'E', 'ė' => 'e',
                'Ę' => 'E', 'ę' => 'e',
                'Ě' => 'E', 'ě' => 'e',
                'Ĝ' => 'G', 'ĝ' => 'g',
                'Ğ' => 'G', 'ğ' => 'g',
                'Ġ' => 'G', 'ġ' => 'g',
                'Ģ' => 'G', 'ģ' => 'g',
                'Ĥ' => 'H', 'ĥ' => 'h',
                'Ħ' => 'H', 'ħ' => 'h',
                'Ĩ' => 'I', 'ĩ' => 'i',
                'Ī' => 'I', 'ī' => 'i',
                'Ĭ' => 'I', 'ĭ' => 'i',
                'Į' => 'I', 'į' => 'i',
                'İ' => 'I', 'ı' => 'i',
                'Ĳ' => 'IJ','ĳ' => 'ij',
                'Ĵ' => 'J', 'ĵ' => 'j',
                'Ķ' => 'K', 'ķ' => 'k',
                'ĸ' => 'k', 'Ĺ' => 'L',
                'ĺ' => 'l', 'Ļ' => 'L',
                'ļ' => 'l', 'Ľ' => 'L',
                'ľ' => 'l', 'Ŀ' => 'L',
                'ŀ' => 'l', 'Ł' => 'L',
                'ł' => 'l', 'Ń' => 'N',
                'ń' => 'n', 'Ņ' => 'N',
                'ņ' => 'n', 'Ň' => 'N',
                'ň' => 'n', 'ŉ' => 'n',
                'Ŋ' => 'N', 'ŋ' => 'n',
                'Ō' => 'O', 'ō' => 'o',
                'Ŏ' => 'O', 'ŏ' => 'o',
                'Ő' => 'O', 'ő' => 'o',
                'Œ' => 'OE','œ' => 'oe',
                'Ŕ' => 'R','ŕ' => 'r',
                'Ŗ' => 'R','ŗ' => 'r',
                'Ř' => 'R','ř' => 'r',
                'Ś' => 'S','ś' => 's',
                'Ŝ' => 'S','ŝ' => 's',
                'Ş' => 'S','ş' => 's',
                'Š' => 'S', 'š' => 's',
                'Ţ' => 'T', 'ţ' => 't',
                'Ť' => 'T', 'ť' => 't',
                'Ŧ' => 'T', 'ŧ' => 't',
                'Ũ' => 'U', 'ũ' => 'u',
                'Ū' => 'U', 'ū' => 'u',
                'Ŭ' => 'U', 'ŭ' => 'u',
                'Ů' => 'U', 'ů' => 'u',
                'Ű' => 'U', 'ű' => 'u',
                'Ų' => 'U', 'ų' => 'u',
                'Ŵ' => 'W', 'ŵ' => 'w',
                'Ŷ' => 'Y', 'ŷ' => 'y',
                'Ÿ' => 'Y', 'Ź' => 'Z',
                'ź' => 'z', 'Ż' => 'Z',
                'ż' => 'z', 'Ž' => 'Z',
                'ž' => 'z', 'ſ' => 's',
                // Decompositions for Latin Extended-B
                'Ș' => 'S', 'ș' => 's',
                'Ț' => 'T', 'ț' => 't',
                // Euro Sign
                '€' => 'E',
                // GBP (Pound) Sign
                '£' => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                'Ơ' => 'O', 'ơ' => 'o',
                'Ư' => 'U', 'ư' => 'u',
                // grave accent
                'Ầ' => 'A', 'ầ' => 'a',
                'Ằ' => 'A', 'ằ' => 'a',
                'Ề' => 'E', 'ề' => 'e',
                'Ồ' => 'O', 'ồ' => 'o',
                'Ờ' => 'O', 'ờ' => 'o',
                'Ừ' => 'U', 'ừ' => 'u',
                'Ỳ' => 'Y', 'ỳ' => 'y',
                // hook
                'Ả' => 'A', 'ả' => 'a',
                'Ẩ' => 'A', 'ẩ' => 'a',
                'Ẳ' => 'A', 'ẳ' => 'a',
                'Ẻ' => 'E', 'ẻ' => 'e',
                'Ể' => 'E', 'ể' => 'e',
                'Ỉ' => 'I', 'ỉ' => 'i',
                'Ỏ' => 'O', 'ỏ' => 'o',
                'Ổ' => 'O', 'ổ' => 'o',
                'Ở' => 'O', 'ở' => 'o',
                'Ủ' => 'U', 'ủ' => 'u',
                'Ử' => 'U', 'ử' => 'u',
                'Ỷ' => 'Y', 'ỷ' => 'y',
                // tilde
                'Ẫ' => 'A', 'ẫ' => 'a',
                'Ẵ' => 'A', 'ẵ' => 'a',
                'Ẽ' => 'E', 'ẽ' => 'e',
                'Ễ' => 'E', 'ễ' => 'e',
                'Ỗ' => 'O', 'ỗ' => 'o',
                'Ỡ' => 'O', 'ỡ' => 'o',
                'Ữ' => 'U', 'ữ' => 'u',
                'Ỹ' => 'Y', 'ỹ' => 'y',
                // acute accent
                'Ấ' => 'A', 'ấ' => 'a',
                'Ắ' => 'A', 'ắ' => 'a',
                'Ế' => 'E', 'ế' => 'e',
                'Ố' => 'O', 'ố' => 'o',
                'Ớ' => 'O', 'ớ' => 'o',
                'Ứ' => 'U', 'ứ' => 'u',
                // dot below
                'Ạ' => 'A', 'ạ' => 'a',
                'Ậ' => 'A', 'ậ' => 'a',
                'Ặ' => 'A', 'ặ' => 'a',
                'Ẹ' => 'E', 'ẹ' => 'e',
                'Ệ' => 'E', 'ệ' => 'e',
                'Ị' => 'I', 'ị' => 'i',
                'Ọ' => 'O', 'ọ' => 'o',
                'Ộ' => 'O', 'ộ' => 'o',
                'Ợ' => 'O', 'ợ' => 'o',
                'Ụ' => 'U', 'ụ' => 'u',
                'Ự' => 'U', 'ự' => 'u',
                'Ỵ' => 'Y', 'ỵ' => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                'ɑ' => 'a',
                // macron
                'Ǖ' => 'U', 'ǖ' => 'u',
                // acute accent
                'Ǘ' => 'U', 'ǘ' => 'u',
                // caron
                'Ǎ' => 'A', 'ǎ' => 'a',
                'Ǐ' => 'I', 'ǐ' => 'i',
                'Ǒ' => 'O', 'ǒ' => 'o',
                'Ǔ' => 'U', 'ǔ' => 'u',
                'Ǚ' => 'U', 'ǚ' => 'u',
                // grave accent
                'Ǜ' => 'U', 'ǜ' => 'u',
            );

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
                ."\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
                ."\xc3\xc4\xc5\xc7\xc8\xc9\xca"
                ."\xcb\xcc\xcd\xce\xcf\xd1\xd2"
                ."\xd3\xd4\xd5\xd6\xd8\xd9\xda"
                ."\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
                ."\xe4\xe5\xe7\xe8\xe9\xea\xeb"
                ."\xec\xed\xee\xef\xf1\xf2\xf3"
                ."\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
                ."\xfc\xfd\xff";

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    private function sanitize_title_with_dashes( $title ) {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        if ($this->seems_utf8($title)) {
            if (function_exists('mb_strtolower')) {
                $title = mb_strtolower($title, 'UTF-8');
            }
            $title = $this->utf8_uri_encode($title, 200);
        }

        $title = strtolower($title);

        //if ( 'save' == $context ) {
        // Convert nbsp, ndash and mdash to hyphens
        $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
        // Convert nbsp, ndash and mdash HTML entities to hyphens
        $title = str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $title );

        // Strip these characters entirely
        $title = str_replace( array(
            // iexcl and iquest
            '%c2%a1', '%c2%bf',
            // angle quotes
            '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
            // curly quotes
            '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
            '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
            // copy, reg, deg, hellip and trade
            '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
            // acute accents
            '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
            // grave accent, macron, caron
            '%cc%80', '%cc%84', '%cc%8c',
        ), '', $title );

        // Convert times to x
        $title = str_replace( '%c3%97', 'x', $title );
        //}

        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '-', $title);

        $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');

        return $title;
    }

    public function convertStringToSlug( $title ) {

        $title = $this->remove_accents($title);

        $title = $this->sanitize_title_with_dashes($title);

        /*if ( '' === $title || false === $title )
            $title = $fallback_title;*/

        return urldecode($title);
    }
    public  function getSlice($html,$char,$addText='...')
    {
        $string=strip_tags($html);
        $getlength=strlen($string);
//        return $getlength;
        if ($getlength > $char) {
            $end=$char/100*20;
//            return $char.'-'.$end.'-'.($char-$end).'-'.stripos($string, ' ', $char-$end);
            $text= substr($string, 0, stripos($string, ' ', $char-$end));
            $text.=$addText;
        } else{
            $text=$string;
        }
        return $text;

    }
    public  function getSliceName($text,$char)
    {
        if(strlen($text)<=$char){
            return $text;
        }else{
            $end =strlen($text) - $char;
            $make=substr($text,0,$char).'...'.substr($text,$end,$char);
            return $make;
        }


    }
    public  function getSliceWord($text,$word)
    {
        $slice='';
        $count=0;
        $items=explode(" ",$text);
        foreach ($items as $item){
            if($count < $word){
                $slice.=$item.' ';
            }else{
                $tripleDot=1;
            }
            $count++;
        }
        if(isset($tripleDot))
            $slice.=' ...';

        return $slice;
    }
    public function orderHash($id=123){
        return md5(uniqid($id.'-'.rand(111,999).time()));
    }
    public function findBankName($number){
        $faBankName='["\u0628\u0627\u0646\u06a9 \u0627\u0642\u062a\u0635\u0627\u062f \u0646\u0648\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u0627\u0646\u0635\u0627\u0631","\u0628\u0627\u0646\u06a9 \u0627\u06cc\u0631\u0627\u0646 \u0632\u0645\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0633\u0627\u0631\u06af\u0627\u062f","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0633\u0627\u0631\u06af\u0627\u062f","\u0628\u0627\u0646\u06a9 \u0622\u06cc\u0646\u062f\u0647","\u0628\u0627\u0646\u06a9 \u062a\u062c\u0627\u0631\u062a","\u0628\u0627\u0646\u06a9 \u062a\u062c\u0627\u0631\u062a","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u062a\u0639\u0627\u0648\u0646","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062d\u06a9\u0645\u062a \u0627\u06cc\u0631\u0627\u0646\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062f\u06cc","\u0628\u0627\u0646\u06a9 \u0631\u0641\u0627\u0647 \u06a9\u0627\u0631\u06af\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0633\u0627\u0645\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0633\u067e\u0647","\u0628\u0627\u0646\u06a9 \u0633\u0631\u0645\u0627\u06cc\u0647","\u0628\u0627\u0646\u06a9 \u0633\u06cc\u0646\u0627","\u0628\u0627\u0646\u06a9 \u0634\u0647\u0631","\u0628\u0627\u0646\u06a9 \u0634\u0647\u0631","\u0628\u0627\u0646\u06a9 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0635\u0646\u0639\u062a \u0648 \u0645\u0639\u062f\u0646","\u0628\u0627\u0646\u06a9 \u0642\u0631\u0636 \u0627\u0644\u062d\u0633\u0646\u0647 \u0645\u0647\u0631 \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0642\u0648\u0627\u0645\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0627\u0631\u0622\u0641\u0631\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0627\u0631\u0622\u0641\u0631\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0634\u0627\u0648\u0631\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u06a9\u0634\u0627\u0648\u0631\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u06af\u0631\u062f\u0634\u06af\u0631\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0631\u06a9\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0633\u06a9\u0646","\u0628\u0627\u0646\u06a9 \u0645\u0644\u062a","\u0628\u0627\u0646\u06a9 \u0645\u0644\u062a","\u0628\u0627\u0646\u06a9 \u0645\u0644\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0647\u0631 \u0627\u0642\u062a\u0635\u0627\u062f","\u067e\u0633\u062a \u0628\u0627\u0646\u06a9 \u0627\u06cc\u0631\u0627\u0646","\u0645\u0648\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u062a\u0648\u0633\u0639\u0647","\u0645\u0648\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u06a9\u0648\u062b\u0631","\u0645\u0624\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u0645\u0644\u0644 (\u0639\u0633\u06a9\u0631\u06cc\u0647 \u0633\u0627\u0628\u0642)","\u0628\u0627\u0646\u06a9 \u0642\u0631\u0636 \u0627\u0644\u062d\u0633\u0646\u0647 \u0631\u0633\u0627\u0644\u062a","\u0628\u0627\u0646\u06a9 \u062e\u0627\u0648\u0631\u0645\u06cc\u0627\u0646\u0647"]';
        $faBankName= json_decode($faBankName,true);
        $bankLogo=['Eghtesad_Novin','Ansar','Iran_Zamin','Parsian','Parsian','Parsian','Pasargad','Pasargad','Ayandeh','Tejarat','Tejarat','Tosee_Taavon','Tosee_Saderat','Tosee_Saderat','Hekmat','Dey','Refah','Saman','Sepah','Sarmayeh','Sina','Shahr','Shahr','Saderat','Sanat_Madan','Mehr_Iran','Ghavamin','Karafarin','Karafarin','Keshavarzi','Keshavarzi','Gardeshgari','no','Maskan','Mellat','Mellat','Melli','Mehr_Eghtesad','Postbank','Tosee','Kosar','Melall','Resalat','Khavar_Mianeh'];
        $bankCard='[627412,627381,505785,622106,639194,627884,639347,502229,636214,627353,585983,502908,627648,207177,636949,502938,589463,621986,589210,639607,639346,502806,504706,603769,627961,606373,639599,627488,502910,603770,639217,505416,636795,628023,610433,991975,603799,639370,627760,628157,505801,606256,504172,505809]';
        $bankCard=json_decode($bankCard,true);
        if(strlen($number)<7){
            return 'ناشناخته';
        }
        $number=substr($number,0,6);
        $key = array_search($number, $bankCard);
        if(intval($key))
            return $faBankName[$key];
        else
            return 'ناشناخته';
    }
    public function findBankLogo($number){
        $faBankName='["\u0628\u0627\u0646\u06a9 \u0627\u0642\u062a\u0635\u0627\u062f \u0646\u0648\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u0627\u0646\u0635\u0627\u0631","\u0628\u0627\u0646\u06a9 \u0627\u06cc\u0631\u0627\u0646 \u0632\u0645\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0631\u0633\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0633\u0627\u0631\u06af\u0627\u062f","\u0628\u0627\u0646\u06a9 \u067e\u0627\u0633\u0627\u0631\u06af\u0627\u062f","\u0628\u0627\u0646\u06a9 \u0622\u06cc\u0646\u062f\u0647","\u0628\u0627\u0646\u06a9 \u062a\u062c\u0627\u0631\u062a","\u0628\u0627\u0646\u06a9 \u062a\u062c\u0627\u0631\u062a","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u062a\u0639\u0627\u0648\u0646","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062a\u0648\u0633\u0639\u0647 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062d\u06a9\u0645\u062a \u0627\u06cc\u0631\u0627\u0646\u06cc\u0627\u0646","\u0628\u0627\u0646\u06a9 \u062f\u06cc","\u0628\u0627\u0646\u06a9 \u0631\u0641\u0627\u0647 \u06a9\u0627\u0631\u06af\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0633\u0627\u0645\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0633\u067e\u0647","\u0628\u0627\u0646\u06a9 \u0633\u0631\u0645\u0627\u06cc\u0647","\u0628\u0627\u0646\u06a9 \u0633\u06cc\u0646\u0627","\u0628\u0627\u0646\u06a9 \u0634\u0647\u0631","\u0628\u0627\u0646\u06a9 \u0634\u0647\u0631","\u0628\u0627\u0646\u06a9 \u0635\u0627\u062f\u0631\u0627\u062a \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0635\u0646\u0639\u062a \u0648 \u0645\u0639\u062f\u0646","\u0628\u0627\u0646\u06a9 \u0642\u0631\u0636 \u0627\u0644\u062d\u0633\u0646\u0647 \u0645\u0647\u0631 \u0627\u06cc\u0631\u0627\u0646","\u0628\u0627\u0646\u06a9 \u0642\u0648\u0627\u0645\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0627\u0631\u0622\u0641\u0631\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0627\u0631\u0622\u0641\u0631\u06cc\u0646","\u0628\u0627\u0646\u06a9 \u06a9\u0634\u0627\u0648\u0631\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u06a9\u0634\u0627\u0648\u0631\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u06af\u0631\u062f\u0634\u06af\u0631\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0631\u06a9\u0632\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0633\u06a9\u0646","\u0628\u0627\u0646\u06a9 \u0645\u0644\u062a","\u0628\u0627\u0646\u06a9 \u0645\u0644\u062a","\u0628\u0627\u0646\u06a9 \u0645\u0644\u06cc","\u0628\u0627\u0646\u06a9 \u0645\u0647\u0631 \u0627\u0642\u062a\u0635\u0627\u062f","\u067e\u0633\u062a \u0628\u0627\u0646\u06a9 \u0627\u06cc\u0631\u0627\u0646","\u0645\u0648\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u062a\u0648\u0633\u0639\u0647","\u0645\u0648\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u06a9\u0648\u062b\u0631","\u0645\u0624\u0633\u0633\u0647 \u0627\u0639\u062a\u0628\u0627\u0631\u06cc \u0645\u0644\u0644 (\u0639\u0633\u06a9\u0631\u06cc\u0647 \u0633\u0627\u0628\u0642)","\u0628\u0627\u0646\u06a9 \u0642\u0631\u0636 \u0627\u0644\u062d\u0633\u0646\u0647 \u0631\u0633\u0627\u0644\u062a","\u0628\u0627\u0646\u06a9 \u062e\u0627\u0648\u0631\u0645\u06cc\u0627\u0646\u0647"]';
        $faBankName= json_decode($faBankName,true);
        $bankLogo=['Eghtesad_Novin','Ansar','Iran_Zamin','Parsian','Parsian','Parsian','Pasargad','Pasargad','Ayandeh','Tejarat','Tejarat','Tosee_Taavon','Tosee_Saderat','Tosee_Saderat','Hekmat','Dey','Refah','Saman','Sepah','Sarmayeh','Sina','Shahr','Shahr','Saderat','Sanat_Madan','Mehr_Iran','Ghavamin','Karafarin','Karafarin','Keshavarzi','Keshavarzi','Gardeshgari','no','Maskan','Mellat','Mellat','Melli','Mehr_Eghtesad','Postbank','Tosee','Kosar','Melall','Resalat','Khavar_Mianeh'];
        $bankCard='[627412,627381,505785,622106,639194,627884,639347,502229,636214,627353,585983,502908,627648,207177,636949,502938,589463,621986,589210,639607,639346,502806,504706,603769,627961,606373,639599,627488,502910,603770,639217,505416,636795,628023,610433,991975,603799,639370,627760,628157,505801,606256,504172,505809]';
        $bankCard=json_decode($bankCard,true);
        if(strlen($number)<7){
            return 'no';
        }
        $number=substr($number,0,6);
        $key = array_search($number, $bankCard);
        if(intval($key))
            return $bankLogo[$key];
        else
            return 'no';
    }
    public function showCard($number){
        $card=str_split($number, 4);
        $text=$card[0].' - ';
        if(isset($card[1])){
            $second=str_split($card[1], 2);
            $text.=$second[0].'** - ';
            $text.='**** - ';
            if(isset($card[3])){
                $text.=$card[3];

            }
        }
        return $text;
    }
    public function lastOfCard($number){
        $card=str_split($number, 4);
        $text='';
        if(isset($card[3])){
            $text.=' - '.$card[3];

        }
        return $text;
    }
}
