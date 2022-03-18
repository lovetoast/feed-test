<?php

function hash_value($value)
{
    return hash('sha256', $value);
}

/*
Relatively simple function to redact an array. I could have used an array map with a lambda function but using PHP 7.0 precludes me from doing so
*/
function redact_data(array $value) : array {
    unset($value['latitude']);
    unset($value['longitude']);
    $value['salt'] =uniqid(mt_rand(), true); //Generate a salt
    $value['email'] = hash_value(strtolower($value['email']) . $value['salt']); //Hash with email - this is an unnecessary step -  password_hash would have been better. Convert to lowercase to ensure consistency of search.
    $new_address = explode(' ',$value['address']);
    foreach ($new_address as &$new_address_word) {
        if (strlen($new_address_word) > 2) {
            $new_address_word = substr($new_address_word, 0, 2) . '*'; //Replace with asterisk
        }
    }
    $value['address'] = implode(' ',$new_address);
    return $value;
};

function fetch_data(string $user = 'dev_test_user',string $pass = 'V8(Zp7K9Ab94uRgmmx2gyuT.') :void { //Returns nothing
    $host = 'https://tst-api.feeditback.com/exam.users';
    $ch = curl_init($host);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $clear_data = JSON_decode(curl_exec($ch),true);
    curl_close($ch);
    $new_redacted = [];
    foreach ($clear_data as $clear) { //Loop over and redact data and add to new array.
        $new_redacted[] = redact_data($clear);
    }
    $user_file = fopen("users.json", "w");
    fwrite($user_file,JSON_encode($new_redacted));
}

fetch_data();