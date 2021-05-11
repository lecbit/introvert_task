<?php
$subdomain = 'artmmarych';
$link = 'https://' . $subdomain . '.amocrm.ru/api/v4/contacts?with=leads';


$data = json_decode(file_get_contents('token_info.json'), true);;

$access_token = $data['accessToken'];

$headers = [
    'Authorization: Bearer ' . $access_token
];
//Получение всех контактов
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$out = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$code = (int)$code;
$errors = [
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
];

try {
    if ($code < 200 || $code > 204) {
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
    }
} catch (\Exception $e) {
    die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

$obj = json_decode($out);

$count_contacts = count($obj->_embedded->contacts);

//Проверка на существование сделки контакта
for ($i = 0; $i < $count_contacts; $i++) {
    if (count($obj->_embedded->contacts[$i]->_embedded->leads) == 0) {
        if (json_decode($out) != 'NULL') {
            $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/tasks';
            $headers = [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ];
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([["complete_till" => 1664784000, "text" => "Контакт без сделок", "entity_id" => $obj->_embedded->contacts[$i]->id, "entity_type" => 'contacts']]));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $out = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $code = (int)$code;
            $errors = array(
                301 => 'Moved permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            );
            try {
                if ($code != 200 && $code != 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
                }
            } catch (Exception $E) {
                die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }
        }
    }
}