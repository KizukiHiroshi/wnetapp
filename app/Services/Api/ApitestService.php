<?php

namespace App\Services\Api;

class ApitestService {
    
    public function Apitest() {
        $sentence = '日本';
        $CURLERR = NULL;
        $appid = 'dj00aiZpPU04dVdqeXB6eHZKTSZzPWNvbnN1bWVyc2VjcmV0Jng9NmY-';
        $headers = [
            "Content-Type: application/json",
            "User-Agent: Yahoo AppID: ".$appid,
        ];
        $data = array(
            'id'        => '1234-1',
            'jsonrpc'   => '2.0',
            'method'    => 'jlp.furiganaservice.furigana',
            'params'    => [
                'q'     => $sentence,
                'grade' => 1
            ],
         );
        $data = json_encode($data);
        $url = 'https://jlp.yahooapis.jp/FuriganaService/V2/furigana';
        $ch = curl_init();
        $options = array(
            // URL
            CURLOPT_URL => $url,
            // HEADER
            CURLOPT_HTTPHEADER => $headers,
            // Method
            CURLOPT_POST => true, // POST
            // body
            CURLOPT_POSTFIELDS => $data,
            // 変数に保存。これがないと即時出力
            CURLOPT_RETURNTRANSFER => true,
            // header出力
            CURLOPT_HEADER => true, 
            // サーバー証明書の検証を行わない
            CURLOPT_SSL_VERIFYPEER => false,
        );
        //set options
        curl_setopt_array($ch, $options);
        $html = curl_exec($ch);
        //ヘッダー取得
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); 
        //ヘッダー切り出し
        $header = substr($html, 0, $header_size);
        //BODY切り出し
        $body = substr($html, $header_size);
        //JSONに変換
        $body = json_decode($body); 

        if(curl_errno($ch)){        //curlでエラー発生
            $CURLERR .= 'curl_errno:' . curl_errno($ch) . "\n";
            $CURLERR .= 'curl_error:' . curl_error($ch) . "\n";
            $CURLERR .= '▼curl_getinfo' . "\n";
            foreach(curl_getinfo($ch) as $key => $val){
                if (is_array($val)) { continue; }
                $CURLERR .= '■' . $key . ':' . $val . "\n";
            }
            echo nl2br($CURLERR);
        }
        curl_close($ch);
        dd($body);
    }
}

