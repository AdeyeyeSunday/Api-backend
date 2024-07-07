<?php

if (!function_exists('jsonResponse')) {
    function jsonResponse($message, $data = null, $status = 200) {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}

// if (!function_exists('curl_post')) {
//     function curl_post($url, $data)
//     {
//         $ch = curl_init();

//         curl_setopt($ch, CURLOPT_URL, $url);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//         curl_setopt($ch, CURLOPT_HTTPHEADER, [
//             'Content-Type: application/json',
//             'Accept: application/json'
//         ]);

//         $result = curl_exec($ch);

//         if (curl_errno($ch)) {
//             throw new \Exception(curl_error($ch));
//         }

//         curl_close($ch);

//         return json_decode($result, true);
//     }
// }

