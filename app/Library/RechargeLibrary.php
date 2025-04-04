<?php

namespace App\library {

    use App\Models\Numberdata;
    use App\Models\Circleprovider;
    use App\Models\Backupapi;
    use App\Models\Provider;
    use App\Models\Apiprovider;
    use App\Models\Commissionreport;
    use App\Models\Apiresponse;
    use App\Models\Providerlimit;
    use App\Library\GetcommissionLibrary;
    use App\Models\Api;
    use App\Models\User;
    use App\Models\Report;
    use Helpers;
    use App\Models\Responsesetting;
    use http\Env\Response;

    class RechargeLibrary
    {

        function recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            $checkspeedlimit = Api::where('id', $api_id)->where('speed_status', 1)->first();
            if ($checkspeedlimit) {
                $providerId = Provider::whereIn('service_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15])->get(['id']);
                $countpending = Report::where('api_id', $api_id)->whereIn('provider_id', $providerId)->where('status_id', 3)->count();
                if ($checkspeedlimit->speed_limit >= $countpending) {
                    return $this->recharge_api_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                } else {
                    $api_type = $api_type + 1;
                    $backupapis = Backupapi::where('provider_id', $provider_id)->where('api_type', $api_type)->where('status_id', 1)->first();
                    if ($backupapis) {
                        return $this->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $backupapis->api_id, $insert_id, $api_type, $payment_mode);
                    } else {
                        $status = 2;
                        $txid = '';
                        $data = '';
                        return array('status' => ucfirst(strtolower($status)), 'ref_id' => $txid, 'txnid' => $txid, 'response' => $data);
                    }
                }
            } else {
                return $this->recharge_api_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
            }
        }

        function recharge_api_middle($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            if (Provider::where('id', $provider_id)->exists()) {
                $providers = Provider::find($provider_id);
                if ($api_id == 1) {
                    if ($providers->service_id == 1 || $providers->service_id == 2 || $providers->service_id == 3) {
                        $dataapi = $this->merchant_pay2all($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                    } else {
                        $dataapi = $this->merchant_pay2all_bbps($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                    }

                    $status = $dataapi['status'];
                    $txid = $dataapi['txnid'];
                    $data = $dataapi['response'];
                    return array('status' => ucfirst(strtolower($status)), 'ref_id' => $txid, 'txnid' => $txid, 'response' => $data);
                } else {
                    $apis = Api::find($api_id);
                    if ($apis) {
                        if ($apis->method == 1) {
                            return $this->get_method_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                        } else {
                            $status = 2;
                            $txid = "Invalid api mothod";
                            $data = "";
                            return array('status' => ucfirst(strtolower($status)), 'ref_id' => $txid, 'txnid' => $txid, 'response' => $data);
                        }

                    } else {
                        $dataapi = $this->merchant_pay2all($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                        $status = $dataapi['status'];
                        $txid = $dataapi['txnid'];
                        $data = $dataapi['response'];
                        return array('status' => ucfirst(strtolower($status)), 'ref_id' => $txid, 'txnid' => $txid, 'response' => $data);
                    }
                }

            } else {
                $dataapi = $this->merchant_pay2all($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
                $status = $dataapi['status'];
                $txid = $dataapi['txnid'];
                $data = $dataapi['response'];
                return array('status' => ucfirst(strtolower($status)), 'ref_id' => $txid, 'txnid' => $txid, 'response' => $data);
            }
        }


        function get_method_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            $apis = Api::find($api_id);
            if ($apis->response_type == 1) {
                return $this->json_response_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
            } elseif ($apis->response_type == 2) {
                return $this->xml_response_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode);
            } else {
                $status = 2;
                $txnid = 'Invalid response type';
                $response = 'Api  Not addedd';
                return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
            }
        }

        function json_response_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            $userDetails = User::find($user_id);
            if ($userDetails->role_id == 10 && $provider_id == 57) {
                $number = $number;
            } elseif ($provider_id == 57) {
                $number = $optional2;
            }

            $apis = Api::find($api_id);
            if ($apis) {
                $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
                if ($apiproviders) {
                    $operator_code = $apiproviders->operator_code;
                    $base_url = $apis->base_url;
                    $myvalue = ["[number]", "[opcode]", "[amount]", "[txnid]", "[optional1]", "[optional2]"];
                    $replacevalue = [$number, $operator_code, $amount, $insert_id, $optional1, $optional2];
                    $endurl = str_replace($myvalue, $replacevalue, $base_url);
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $endurl);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $response = curl_exec($curl);
                    curl_close($curl);
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $endurl]);
                    $library = new GetcommissionLibrary();
                    $commission = $library->recharge_api_commission($api_id, $amount, $provider_id);
                    $api_commission = $commission['api_commission'];
                    Report::where('id', $insert_id)->update(['api_id' => $api_id, 'api_comm' => $api_commission]);

                    $res = json_decode($response, true);
                    // success response
                    $responsesettingssuccess = Responsesetting::where('api_id', $api_id)->where('status_id', 1)->get();
                    foreach ($responsesettingssuccess as $value) {
                        if (!empty($value->under_value)) {
                            $status = $res["$value->under_value"]["$value->status_parameter"];
                        } else {
                            $status = $res["$value->status_parameter"];
                        }

                        if ($status == $value->status_value) {
                            $status = 1;
                            if (!empty($value->under_value)) {
                                $txnid = $res["$value->under_value"]["$value->operator_ref_parameter"];
                            } else {
                                $txnid = $res["$value->operator_ref_parameter"];
                            }

                            $providerlimit = Providerlimit::where('user_id', $user_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                            if ($providerlimit) {
                                Providerlimit::where('id', $providerlimit->id)->decrement('amount_limit', $amount);
                            }
                            return array('status' => $status, 'ref_id' => '', 'txnid' => $txnid, 'response' => $response);
                        }

                    }
                    // failure response
                    $responsesettingsfailure = Responsesetting::where('api_id', $api_id)->where('status_id', 2)->get();
                    foreach ($responsesettingsfailure as $value) {
                        if (!empty($value->under_value)) {
                            $status = $res["$value->under_value"]["$value->status_parameter"];
                        } else {
                            $status = $res["$value->status_parameter"];
                        }
                        if ($status == $value->status_value) {
                            $api_type = $api_type + 1;
                            $backupapis = Backupapi::where('provider_id', $provider_id)->where('api_type', $api_type)->where('status_id', 1)->first();
                            if ($backupapis) {
                                return $this->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $backupapis->api_id, $insert_id, $api_type, $payment_mode);
                            } else {
                                if (!empty($value->under_value)) {
                                    $txnid = $res["$value->under_value"]["$value->operator_ref_parameter"];
                                } else {
                                    $txnid = $res["$value->operator_ref_parameter"];
                                }
                                $status = 2;
                                $txnid = $txnid;
                            }
                            return array('status' => $status, 'ref_id' => '', 'txnid' => $txnid, 'response' => $response);
                        }

                    }

                    return array('status' => 3, 'ref_id' => '', 'txnid' => '', 'response' => $response);

                } else {
                    $status = 2;
                    $txnid = 'Provider not added';
                    $response = 'Provider not added';
                }
                return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
            } else {
                $status = 2;
                $txnid = 'Something went wrong';
                $response = 'Api  Not addedd';
                return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
            }
        }

        function xml_response_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            if ($provider_id == 57) {
                $number = $optional2;
            }
            $apis = Api::find($api_id);
            if ($apis) {
                $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
                if ($apiproviders) {
                    $operator_code = $apiproviders->operator_code;
                    $base_url = $apis->base_url;
                    $myvalue = ["[number]", "[opcode]", "[amount]", "[txnid]", "[optional1]", "[optional2]"];
                    $replacevalue = [$number, $operator_code, $amount, $insert_id, $optional1, $optional2];
                    $endurl = str_replace($myvalue, $replacevalue, $base_url);
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $endurl);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                    $response = curl_exec($curl);
                    curl_close($curl);
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $endurl]);
                    $library = new GetcommissionLibrary();
                    $commission = $library->recharge_api_commission($api_id, $amount, $provider_id);
                    $api_commission = $commission['api_commission'];
                    Report::where('id', $insert_id)->update(['api_id' => $api_id, 'api_comm' => $api_commission]);
                    $xmlres = simplexml_load_string($response);
                    $resjsonencode = json_encode($xmlres);
                    $res = json_decode($resjsonencode, true);
                    // success response
                    $responsesettingssuccess = Responsesetting::where('api_id', $api_id)->where('status_id', 1)->get();
                    foreach ($responsesettingssuccess as $value) {
                        $status = $res["$value->status_parameter"];
                        if ($status == $value->status_value) {
                            $status = 1;
                            $txnid = $res["$value->operator_ref_parameter"];
                            $providerlimit = Providerlimit::where('user_id', $user_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                            if ($providerlimit) {
                                Providerlimit::where('id', $providerlimit->id)->decrement('amount_limit', $amount);
                            }
                            return array('status' => $status, 'ref_id' => '', 'txnid' => $txnid, 'response' => $response);
                        }

                    }
                    // failure response
                    $responsesettingsfailure = Responsesetting::where('api_id', $api_id)->where('status_id', 2)->get();
                    foreach ($responsesettingsfailure as $value) {
                        $status = $res["$value->status_parameter"];
                        if ($status == $value->status_value) {
                            $api_type = $api_type + 1;
                            $backupapis = Backupapi::where('provider_id', $provider_id)->where('api_type', $api_type)->where('status_id', 1)->first();
                            if ($backupapis) {
                                return $this->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $backupapis->api_id, $insert_id, $api_type, $payment_mode);
                            } else {
                                $status = 2;
                                $txnid = $res["$value->operator_ref_parameter"];
                            }
                            return array('status' => $status, 'ref_id' => '', 'txnid' => $txnid, 'response' => $response);
                        }

                    }
                    // pending response
                    return array('status' => 3, 'ref_id' => '', 'txnid' => '', 'response' => $response);

                } else {
                    $status = 2;
                    $txnid = 'Provider not added';
                    $response = 'Provider not added';
                }
                return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
            } else {
                $status = 2;
                $txnid = 'Something went wrong';
                $response = 'Api  Not addedd';
                return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
            }
        }


        function merchant_pay2all($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            $library = new GetcommissionLibrary();
            $commission = $library->recharge_api_commission($api_id, $amount, $provider_id);
            $api_commission = $commission['api_commission'];
            $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
            if ($apiproviders) {
                $operator_code = $apiproviders->operator_code;
                $apis = Api::where('id', $api_id)->first();
                if ($apis) {
                    $api_key = 'Bearer ' . $apis->api_key;
                    $apiurl = "";
                    $api_request_parameters = array(
                        'number' => $number,
                        'provider_id' => $operator_code,
                        'amount' => $amount,
                        'client_id' => $insert_id,
                        'optional1' => $optional1
                    );
                    $method = 'POST';
                    $header = ["Accept:application/json", "Authorization:" . $api_key];
                    $response = Helpers::pay_curl_post($apiurl, $header, $api_request_parameters, $method);
                    $api_request_parameters = json_encode($api_request_parameters);
                    $request_message = $apiurl . '?' . $api_request_parameters;
                    Report::where('id', $insert_id)->update(['api_id' => $api_id]);
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $request_message]);
                    Report::where('id', $insert_id)->update(['api_id' => $api_id, 'api_comm' => $api_commission]);
                    $res = json_decode($response);
                    $message = (empty($res->message)) ? '' : $res->message;
                    if ($message == 'Unauthenticated.') {
                        return array('status' => 3, 'ref_id' => 'Unauthenticated access', 'txnid' => 'Unauthenticated access', 'response' => 'Unauthenticated access');
                    }
                    $status = $res->status;
                    if ($status == 0 || $status == 1) {
                        if ($res->status_id == 0 || $res->status_id == 1) {
                            $status = 1;
                            $txnid = $res->utr;
                            $providerlimit = Providerlimit::where('user_id', $user_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                            if ($providerlimit) {
                                Providerlimit::where('id', $providerlimit->id)->decrement('amount_limit', $amount);
                            }
                        } elseif ($res->status_id == 2) {
                            $api_type = $api_type + 1;
                            $backupapis = Backupapi::where('provider_id', $provider_id)->where('api_type', $api_type)->where('status_id', 1)->first();
                            if ($backupapis) {
                                return $this->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $backupapis->api_id, $insert_id, $api_type, $payment_mode);
                            } else {
                                $status = 2;
                                $txnid = '';
                            }
                        } else {
                            $status = 3;
                            $txnid = '';
                        }
                    } elseif ($status == 2) {
                        $api_type = $api_type + 1;
                        $backupapis = Backupapi::where('provider_id', $provider_id)->where('api_type', $api_type)->where('status_id', 1)->first();
                        if ($backupapis) {
                            return $this->recharge_api($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $backupapis->api_id, $insert_id, $api_type, $payment_mode);
                        } else {
                            $status = 2;
                            $txnid = '';
                        }
                    } else {
                        $status = 3;
                        $txnid = '';
                    }
                } else {
                    $status = 2;
                    $txnid = 'Api  Not addedd';
                    $response = 'Api  Not addedd';
                }
            } else {
                $status = 2;
                $txnid = 'Api Provider Not addedd';
                $response = 'Api Provider Not addedd';
            }

            return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
        }

        function merchant_pay2all_bbps($number, $amount, $provider_id, $optional1, $optional2, $optional3, $optional4, $client_id, $user_id, $api_id, $insert_id, $api_type, $payment_mode)
        {
            $library = new GetcommissionLibrary();
            $commission = $library->recharge_api_commission($api_id, $amount, $provider_id);
            $api_commission = $commission['api_commission'];

            $providers = Provider::where('id', $provider_id)->first();
            $apiproviders = Apiprovider::where('api_id', $api_id)->where('provider_id', $provider_id)->first();
            $userdetails = User::find($user_id);
            if ($providers) {
                if ($providers->merchant_pay2all) {
                    $apis = Api::where('id', 1)->first();
                    $merchant_pay2all = $providers->merchant_pay2all;
                    $api_key = 'Bearer ' . $apis->api_key;
                    $apiurl = "";
                    $api_request_parameters = array(
                        'number' => $userdetails->mobile,
                        'provider_id' => $merchant_pay2all,
                        'amount' => $amount,
                        'client_id' => $insert_id,
                        'optional1' => $optional1,
                        'optional2' => $optional2,
                        'optional3' => $optional3,
                        'reference_id' => $optional4,
                        'payment_mode' => $payment_mode,
                        'api_id' => 27,
                    );
                    $method = 'POST';
                    $header = ["Accept:application/json", "Authorization:" . $api_key];
                    $response = Helpers::pay_curl_post($apiurl, $header, $api_request_parameters, $method);
                    $api_request_parameters = json_encode($api_request_parameters);
                    $request_message = $apiurl . '?' . $api_request_parameters;
                    Report::where('id', $insert_id)->update(['api_id' => $api_id]);
                    Apiresponse::insertGetId(['message' => $response, 'api_type' => $api_id, 'report_id' => $insert_id, 'request_message' => $request_message]);
                    Report::where('id', $insert_id)->update(['api_id' => $api_id, 'api_comm' => $api_commission]);
                    $res = json_decode($response);
                    $message = (empty($res->message)) ? '' : $res->message;
                    if ($message == 'Unauthenticated.') {
                        return array('status' => 3, 'ref_id' => 'Unauthenticated access', 'txnid' => 'Unauthenticated access', 'response' => 'Unauthenticated access');
                    }
                    $status = $res->status;
                    if ($status == 0 || $status == 1) {
                        $status = 1;
                        $txnid = $res->utr;
                        $providerlimit = Providerlimit::where('user_id', $user_id)->where('provider_id', $provider_id)->where('status_id', 1)->first();
                        if ($providerlimit) {
                            Providerlimit::where('id', $providerlimit->id)->decrement('amount_limit', $amount);
                        }
                    } elseif ($status == 2) {
                        $status = 2;
                        $txnid = '';
                    } else {
                        $status = 3;
                        $txnid = '';
                    }
                } else {
                    $status = 2;
                    $txnid = 'Servicec not activate';
                    $response = 'Servicec not activate';
                }
            } else {
                $status = 2;
                $txnid = 'Invalid provider id';
                $response = 'Invalid provider id';
            }
            return array('status' => $status, 'ref_id' => $txnid, 'txnid' => $txnid, 'response' => $response);
        }


    }
}
