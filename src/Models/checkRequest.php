<?php
namespace Models;
require_once('normalizeJson.php');

class checkRequest
{
    public function validate($input_data, $reqFields = [])
    {
        $result = [];
        $json_error = [];
        $error = [];
        $data = $input_data->getBody();
        if ($data == '') {
            $post_data = $input_data->getParsedBody();
        } else {
            $toJson = new normilizeJson();
            $data = $toJson->normalizeJson($data);
            $data = str_replace('\"', '"', $data);
            $post_data = json_decode($data, true);
        }
        if (json_last_error() != 0) {
            $json_error[] = json_last_error_msg() . '. Incorrect input JSON. Please, check fields with JSON input.';
        }
        if (!empty($json_error)) {
            $result['callback'] = 'error';
            $result['contextWrites']['to']['status_code'] = 'JSON_VALIDATION';
            $result['contextWrites']['to']['status_msg'] = implode(',', $json_error);
        } else {
            if (!empty($reqFields)) {
                foreach ($reqFields as $item) {
                    if (isset($post_data['args'][$item])) {
                        if (is_array($post_data['args'][$item])) {
                            if (empty($post_data['args'][$item])) {
                                $error[] = $item;
                            }
                        }
                        elseif (!is_bool($post_data['args'][$item])) {
                            if (strlen($post_data['args'][$item]) == 0) {
                                $error[] = $item;
                            }
                        }
                    }
                    else {
                        $error[] = $item;
                    }
                }
            }
            if (!empty($error)) {
                $result['callback'] = 'error';
                $result['contextWrites']['to']['status_code'] = "REQUIRED_FIELDS";
                $result['contextWrites']['to']['status_msg'] = "Please, check and fill in required fields.";
                $result['contextWrites']['to']['fields'] = $error;
            }
        }
        if (empty($result)) {
            $result = $post_data;
        }

        return $result;
    }
}