<?php
    //header('Content-Type: application/json');
    $request = file_get_contents('php://input');
    $req_dump = print_r($request, true);
    $results = json_decode($req_dump);
    $fp = file_put_contents('listener.log', print_r($req_dump, true));

    if($results) {
        
        $collector_id = $results->resources->collector_id;
        $response_id = $results->resources->respondent_id;

        include_once($_SERVER['DOCUMENT_ROOT'].'/includes/request-header.php');
        $url = 'https://api.surveymonkey.net/v3/collectors/'.$collector_id.'/responses/'.$response_id.'/details';
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $results_json = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($results_json, true);

        // GET THE VARIABLES FOR OUR EMAIL MESSAGE
        $response_status = $results['response_status'];
        $name = $results['pages'][0]['questions'][0]['answers'][0]['text'];
        $emailTo = $results['pages'][0]['questions'][0]['answers'][1]['text'];
        $total_questions = $results['quiz_results']['total_questions'];
        $quiz_correct = $results['quiz_results']['correct'];
        $quiz_calculation = $quiz_correct/$total_questions*100;

        // format calculation if decimal
        if(is_numeric($quiz_calculation) && floor($quiz_calculation) != $quiz_calculation) {
            $quiz_score = number_format((float)$quiz_calculation, 2, '.', '') . '%';
        } else {
            $quiz_score = $quiz_calculation . '%';
        }

        // get the quiz title based off $collector_id
        switch ($collector_id) {
            case "228639627":
                $quiz_title = "Monday - Sustainability";
                break;
            case "228643083":
                $quiz_title = "Tuesday - Rocks with Ryan";
                break;
            case "228643596":
                $quiz_title = "Wednesday - Native Plants";
                break;
            case "228644266":
                $quiz_title = "Thursday - Raptors";
                break;
            case "228646145":
                $quiz_title = "Friday - Turtle Time";
                break;
        }

        if($response_status == 'completed') {

            // BUILD THE EMAIL
            $subject = 'Earthweek Quiz ' . $quiz_title . ': Completed';
            // the message
            $message = '<html><body style="background-color: #FFF; padding: 0; margin: 0;">';
            $message .= '<table width="100%" cellspacing="0" cellpadding="0"><tbody>';
            $message .= '<tr><td></td><td width="600">';
            $message .= '<table width="100%" cellspacing="0" cellpadding="20" style="font-family: sans-serif; border: solid 2px #666; text-align: center;"><tbody>';
            $message .= '<tr><td colspan="2"><img width="80%" height="auto" src="https://smscland.org/images/smsc-earthweek-sml.png">'; 
            $message .= '<p style="text-align: center;"><span style="font-size: 14px">SMSC EARTHWEEK 2019 QUIZ</span><br />';
            $message .= '<strong style="font-size: 30px">CERTIFICATE OF COMPLETION<strong></p>';
            $message .= '<p><strong  style="font-size: 30px">'. $name .'</strong><br /><strong style="font-size: 20px; color: #666;">'. $quiz_title .'</strong></p></td></tr>';
            $message .= '<tr><td width="55%" style="vertical-align: middle; text-align: right;"><strong>YOUR SCORE:</strong><br/><strong style="font-size: 60px">'.$quiz_score.'</strong></td>';
            $message .= '<td width="45%"  style="vertical-align: middle; text-align: left;"><img width="80%" height="auto" src="https://smscland.org/images/earthweek-badge.png"></td></tr>';
            $message .= '</tbody></table></td><td></td></tr>';
            $message .= '</tbody></table></body></html>';

            // the header
            $headers = 'MIME-Version: 1.0'. "\r\n";
            $headers .= 'Content-Type: text/html; charset=ISO-8859-1\r\n' . "\r\n";
            $headers .= 'From: SMSCLand.org <marketing@shakopeedakota.org>' . "\r\n";
            $headers .= 'Bcc: dave.vang@shakopeedakota.org' . "\r\n";

            // send email
            mail($emailTo, $subject, $message, $headers);
            
            $log = 'mailedTo.log';
            $handle = fopen($log, 'a') or die ('Cannot open file: ' .$log);
            fwrite($handle, '\n'.$emailTo);
            fclose($handle);
        }
    } else {
        $log = 'fail.log';
        $handle = fopen($log, 'a') or die ('Cannot open file: ' .$log);
        fwrite($handle, 'no results');
        fwrite($handle, '\n');
        fclose($handle);
    }


    //$fp = file_put_contents('results6.log', print_r($results, true));

    //$json_data = json_decode($data);
    //$obj = $json_data->payload;
    //print_r($obj);
    // Assemble the body of the email...
    /*
    $message_body = <<<EOM
    first name: $fname \n
    last name: $lname \n
    nationbuilder_id: $nid \n
    EOM;
    mail('someone@example.com','NB Webhook Data',$message_body);
    */
?>
