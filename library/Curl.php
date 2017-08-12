<?php

namespace App;

class Curl
{
    
    public function request($url, $data = [], $type = false, $headers = array())
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => '',       // handle all encodings
            CURLOPT_USERAGENT      => 'spider', // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );
        
        if ($type == 'json') {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER] = array_merge(array('Content-Type:application/json'), $headers);
        } else {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        
        // var_dump(http_build_query($data));

        $ch      = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);
        
        $header['content'] = $content;
        $header['err']     = $err;
        $header['errmsg']  = $errmsg;
        
        return $header;
    }
}
