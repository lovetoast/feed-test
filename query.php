<?php
function hash_value($value)
{
    return hash('sha256', $value);
}


function group_array(array $user_data,string $common_key) : array {
    $output_array = [];
    $total_ages = 0;
    $about_words = [];
    foreach ( $user_data as $record ) {
        if ( !key_exists($record[$common_key], $output_array) ) {
          $output_array[$record[$common_key]] = 0;
        }
        $output_array[$record[$common_key]] += 1; //Compile an array of colours
        $about_array = explode(' ',$record['about']); // Explode the about key, and then loop to find words
        foreach ($about_array as $words) {
            if ( !key_exists($words, $about_words) ) {
                $about_words[$words] = 0;
            } 
            $about_words[$words] += 1;

        }
        $total_ages += $record['age']; // Find total ages, could have used a single line function here, but given we are looping regardless, it is simpler to just put addition here.
    }
    $report_data = ['words'=>$about_words,'common_colours'=>array_keys($output_array, max($output_array)),'average_age'=>number_format((float)$total_ages/count($user_data), 2, '.', '')];
    return $report_data;  

}
//We could have also inverted this and checked for the value of $exact and then went through the $field queried
function query(string $field, string $value, bool $exact = TRUE) :void 
{
    $user_json_data = JSON_decode(file_get_contents("user_data.json"),true);
    $allowed_fields = ['id','status','age','eye_color','first_name','last_name','gender','company','email','phone','address','about','created','favorite_color'];
    $can_partial_stripos = ['id','status','first_name','last_name','company','phone','about','address'];
    $not_partial = ['email','eye_color','favorite_color'];
    if (!in_array($field,$allowed_fields)) {
        echo '<br><b> Invalid filter<br></b>';
        return;
    }
    if (in_array($field,$not_partial) && !$exact) {
        echo '<br><b> Cannot partial on  ' . $field . ' for ' . $value . ' <br></b>';
        return;

    }
    $results = array_filter($user_json_data,function($user) use ($field,$can_partial_stripos,$allowed_fields,$not_partial,$user_json_data,$exact,$value)  {
        if ($field == 'created') { //Partial match on created involves day check
            if ($exact) {
                return $user['created'] == $value;
            } else {
                return date('Y-m-d',strtotime($user['created']) == date('Y-m-d',strtotime($value)));
            }
        } else if ($field == 'age') { //Partial match on age is a range
            if ($exact) {
                return $user['age'] == $value;
            } else {
                return $user['age'] > $value + 2 &&  $user['age'] < $value - 2;
            }
        } else if (in_array($field,$can_partial_stripos)) { //Other partial matches based on stripos (for case insensitive search)
            if ($exact) 
                return $user[$field] == $value;
            else
                return stripos($user[$field],$value);
        } else if (in_array($field,$not_partial)) { //Email - check against salt
            if ($exact) {
                if ($field == 'email')
                    return $user['email'] == hash('sha256',strtolower($value) . $user['salt']); //
                else   
                    return $user[$field] == $value;
            } 
        } 
    });
    $exact_str = $exact ? 'Exact search: ' : 'Partial search on: ';
    if ($results) {
        echo '<b><br>' . $exact_str . 'on ' . $field . ' for ' . $value . ' :</b><br>';
        foreach ($results as $result) {
            echo '<br>' . $result['first_name'] . ' ' . $result['last_name']; // Echo name
        }
    } else {
        echo '<b><br>No data for: ' . $exact_str . 'on ' . $field . ' for ' . $value . ' :</b><br>';

    }
    echo '<br>';
}

function report()
{
    $user_json_data = JSON_decode(file_get_contents("users.json"),true); //Not sure why we are hyphenating now rather than underscore - but this was in the md file
    $report_data = group_array($user_json_data,'favorite_colour');
    usort($user_json_data, function($a, $b) {
        return strtotime($a['created']) - strtotime($b['created']);
    });
    $report_data['oldest_user'] = $user_json_data[0]['first_name'] . ' ' . $user_json_data[0]['last_name'];
    $report_data['newest_user'] = $user_json_data[array_key_last($user_json_data)]['first_name'] . ' ' . $user_json_data[array_key_last($user_json_data)]['last_name'];

    $user_file = fopen("users-report.json", "w");
    fwrite($user_file,JSON_encode($report_data));



}
query('email', 'mcconnellbranch@zytrek.com',true);
query('id', '5be5884a7ab109472363c6cd');
query('id', '5be5884a331b2c695', FALSE);
query('id', '5be5884a331b24639s3cc695');
query('age', '22');
query('age', '20');
query('about', 'exa', FALSE);
query('about', 'ace', FALSE);
query('email', 'mcconnellbranch@zytrek.com');
query('email', 'ryansand@xandem.com');
query('email', 'edwinachang', FALSE);

report();

