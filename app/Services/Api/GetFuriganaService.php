<?php

namespace App\Services\Api;

class GetFuriganaService {
    
    public function GetFurigana($sentence) {
        $CURLERR = '';
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
        if(curl_errno($ch)){ $CURLERR = 'APIエラー'; }
        curl_close($ch);
        usleep(500000);
        if ($CURLERR <> '') {
            return $CURLERR;
        }
        if (!isset($body->result)) {
            return $sentence;
        }
        $word = $body->result->word;
        $furigana = '';
        foreach ($word AS $values) {
            if (isset($values->furigana)) {
                $furigana .= $values->furigana;
            } else {
                $furigana .= $values->surface;
            }
        }
        $furigana = mb_convert_kana($furigana, "KVC");
        return $furigana;
    }
}

