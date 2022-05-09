<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeには有効なURLを指定してください。',
    'after'  => ':attributeには:date以降の日付を指定してください。',
    'after_or_equal' => ':attributeには:dateかそれ以降の日付を指定してください。',
    'alpha'  => ':attributeには英字のみからなる文字列を指定してください。',
    'alpha_dash' => ':attributeには英数字・ハイフン・アンダースコアのみからなる文字列を指定してください。',
    'alpha_num'  => ':attributeには英数字のみからなる文字列を指定してください。',
    'array'  => ':attributeには配列を指定してください。',
    'before' => ':attributeには:date以前の日付を指定してください。',
    'before_or_equal'  => ':attributeには:dateかそれ以前の日付を指定してください。',
    'between'  => [
        'numeric' => ':attributeには:min?:maxまでの数値を指定してください。',
        'file'  => ':attributeには:min?:max KBのファイルを指定してください。',
        'string'  => ':attributeには:min?:max文字の文字列を指定してください。',
        'array' => ':attributeには:min?:max個の要素を持つ配列を指定してください。',
    ],
    'boolean'  => ':attributeには真偽値を指定してください。',
    'confirmed'  => ':attributeが確認用の値と一致しません。',
    'current_password' => 'パスワードが不正です。',
    'date' => ':attributeには正しい形式の日付を指定してください。',
    'date_equals' => ':attribute は :date と同じにしてください。',
    'date_format'  => '":format"という形式の日付を指定してください。',
    'different'  => ':attributeには:otherとは異なる値を指定してください。',
    'digits' => ':attributeには:digits桁の数値を指定してください。',
    'digits_between' => ':attributeには:min?:max桁の数値を指定してください。',
    'dimensions' => ':attributeの画像サイズが不正です。',
    'distinct' => '指定された:attributeは既に存在しています。',
    'email'  => ':attributeには正しい形式のメールアドレスを指定してください。',
    'ends_with' => ':attribute は :values で終わってください。',
    'exists' => '指定された:attributeは存在しません。',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeには空でない値を指定してください。',
    'gt' => [
        'numeric' => ':attribute は :value より大きくしてください。',
        'file' => ':attribute は :value キロバイトより大きくしてください。.',
        'string' => ':attribute は :value 文字より大きくしてください。',
        'array' => ':attribute は :value 個より大きくしてください。',
    ],
    'gte' => [
        'numeric' => ':attribute は :value 以上にしてください。',
        'file' => ':attribute は :value キロバイト以上にしてください。.',
        'string' => ':attribute は :value 文字以上にしてください。',
        'array' => ':attribute は :value 個以上にしてください。',
    ],
    'image'  => ':attributeには画像ファイルを指定してください。',
    'in' => ':attributeには:valuesのうちいずれかの値を指定してください。',
    'in_array' => ':attributeが:otherに含まれていません。',
    'integer'  => ':attributeには整数を指定してください。',
    'ip' => ':attributeには正しい形式のIPアドレスを指定してください。',
    'ipv4' => ':attributeには正しい形式のIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには正しい形式のIPv6アドレスを指定してください。',
    'json' => ':attributeには正しい形式のJSON文字列を指定してください。',
    'lt' => [
        'numeric' => ':attribute は :value より小さくしてください。',
        'file' => ':attribute は :value キロバイトより小さくしてください。.',
        'string' => ':attribute は :value 文字より小さくしてください。',
        'array' => ':attribute は :value 個より小さくしてください。',
    ],
    'lte' => [
        'numeric' => ':attribute は :value 以下にしてください。',
        'file' => ':attribute は :value キロバイト以下にしてください。.',
        'string' => ':attribute は :value 文字以下にしてください。',
        'array' => ':attribute は :value 個以下にしてください。',
    ],
    'max' => [
        'numeric' => ':attributeには:max以下の数値を指定してください。',
        'file'  => ':attributeには:max KB以下のファイルを指定してください。',
        'string'  => ':attributeには:max文字以下の文字列を指定してください。',
        'array' => ':attributeには:max個以下の要素を持つ配列を指定してください。',
    ],
    'mimes' => ':attributeには:valuesのうちいずれかの形式のファイルを指定してください。',
    'mimetypes'  => ':attributeには:valuesのうちいずれかの形式のファイルを指定してください。',
    'min' => [
        'numeric' => ':attributeには:min以上の数値を指定してください。',
        'file'  => ':attributeには:min KB以上のファイルを指定してください。',
        'string'  => ':attributeには:min文字以上の文字列を指定してください。',
        'array' => ':attributeには:min個以上の要素を持つ配列を指定してください。',
    ],
    'multiple_of' => ':attribute は :value の複数形にしてください。',
    'not_in' => ':attributeには:valuesのうちいずれとも異なる値を指定してください。',
    'not_regex' => ':attribute の形式が不正です。',
    'numeric'  => ':attributeには数値を指定してください。',
    'password' => 'パスワードが不正です。',
    'present'  => ':attributeには現在時刻を指定してください。',
    'regex'  => '正しい形式の:attributeを指定してください。',
    'required' => ':attributeは必須です。',
    'required_if'  => ':otherが:valueの時:attributeは必須です。',
    'required_unless'  => ':otherが:values以外の時:attributeは必須です。',
    'required_with'  => ':valuesのうちいずれかが指定された時:attributeは必須です。',
    'required_with_all'  => ':valuesのうちすべてが指定された時:attributeは必須です。',
    'required_without' => ':valuesのうちいずれかがが指定されなかった時:attributeは必須です。',
    'required_without_all' => ':valuesのうちすべてが指定されなかった時:attributeは必須です。',
    'prohibited' => ':attribute 項目は禁止されています。',
    'prohibited_if' => ':attribute 項目は :other が :value のとき禁止されています。',
    'prohibited_unless' => ':attribute 項目は :other が :value ではないとき禁止されています。',
    'same' => ':attributeが:otherと一致しません。',
    'size' => [
        'numeric' => ':attributeには:sizeを指定してください。',
        'file'  => ':attributeには:size KBのファイルを指定してください。',
        'string'  => ':attributeには:size文字の文字列を指定してください。',
        'array' => ':attributeには:size個の要素を持つ配列を指定してください。',
    ],
    'starts_with' => ':attribute は :values で始めてください。',
    'string' => ':attributeには文字列を指定してください。',
    'timezone' => ':attributeには正しい形式のタイムゾーンを指定してください。',
    'unique' => 'その:attributeはすでに使われています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url'  => ':attributeには正しい形式のURLを指定してください。',
    'uuid' => ':attribute は UUID でなければいけません。',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'address1'      => '住所1',
        'approved_at'   => '承認日時',
        'approved_by'   => '承認者',
        'created_at'    => '作成日時',
        'created_by'    => '作成者',
        'deleted_at'    => '削除日時',
        'email'         => 'メールアドレス',
        'end_on'        => '終了日',
        'faxno'         => 'FAX',
        'importance'    => '重要度',
        'name'          => '名前',
        'name_kana'     => 'カナ',
        'name_mei'      => '名',
        'name_sei'      => '姓',
        'name_short'    => '略称',
        'password'      => 'パスワード',
        'postalcode'    => '郵便番号',
        'solution'      => '解決策',
        'start_on'      => '開始日',
        'telno'         => '電話',
        'updated_at'    => '更新日時',
        'updated_by'    => '更新者',
    ],

];
