<?php

use App\Models\Company;
use App\Models\Credentials;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    public static function company_id()
    {
        $website = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'trustxpay..in';
        if ($website) {
            return Company::where('company_website', $website)->firstOrFail();
        }
    }

    public static function pay_curl_xml($url, $xml)
    {
        $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xml),
            "Connection: close",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        return $data;
    }

    public static function pay_curl_get($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    public static function pay_curl_post($url, $header, $parameters, $method)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        return $response;
    }

    public static function send_sms_msg($number, $message)
    {
        $message = urlencode($message);
        $url = "https://control.msg91.com/api/sendhttp.php?authkey=43466ADvfq19mpb52b33cd2&mobiles=$number&message=$message&sender=PAYTWO&route=4&country=91";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    public static function convertNumber($number)
    {
        $num = (string)((int)$number);

        if ((int)($num) && ctype_digit($num)) {
            $words = array();

            $num = str_replace(array(',', ' '), '', trim($num));

            $list1 = array(
                '',
                'one',
                'two',
                'three',
                'four',
                'five',
                'six',
                'seven',
                'eight',
                'nine',
                'ten',
                'eleven',
                'twelve',
                'thirteen',
                'fourteen',
                'fifteen',
                'sixteen',
                'seventeen',
                'eighteen',
                'nineteen'
            );

            $list2 = array(
                '',
                'ten',
                'twenty',
                'thirty',
                'forty',
                'fifty',
                'sixty',
                'seventy',
                'eighty',
                'ninety',
                'hundred'
            );

            $list3 = array(
                '',
                'thousand',
                'million',
                'billion',
                'trillion',
                'quadrillion',
                'quintillion',
                'sextillion',
                'septillion',
                'octillion',
                'nonillion',
                'decillion',
                'undecillion',
                'duodecillion',
                'tredecillion',
                'quattuordecillion',
                'quindecillion',
                'sexdecillion',
                'septendecillion',
                'octodecillion',
                'novemdecillion',
                'vigintillion'
            );

            $num_length = strlen($num);
            $levels = (int)(($num_length + 2) / 3);
            $max_length = $levels * 3;
            $num = substr('00' . $num, -$max_length);
            $num_levels = str_split($num, 3);

            foreach ($num_levels as $num_part) {
                $levels--;
                $hundreds = (int)($num_part / 100);
                $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' Hundred' . ($hundreds == 1 ? '' : 's') . ' ' : '');
                $tens = (int)($num_part % 100);
                $singles = '';

                if ($tens < 20) {
                    $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
                } else {
                    $tens = (int)($tens / 10);
                    $tens = ' ' . $list2[$tens] . ' ';
                    $singles = (int)($num_part % 10);
                    $singles = ' ' . $list1[$singles] . ' ';
                }
                $words[] = $hundreds . $tens . $singles . (($levels && (int)($num_part)) ? ' ' . $list3[$levels] . ' ' : '');
            }
            $commas = count($words);
            if ($commas > 1) {
                $commas = $commas - 1;
            }

            $words = implode(', ', $words);

            $words = trim(str_replace(' ,', ',', ucwords($words)), ', ');
            if ($commas) {
                $words = str_replace(',', ' and', $words);
            }
        } else if (!((int)$num)) {
            $words = 'Zero';
        } else {
            $words = '';
        }

        return $words;
    }

    public static function generateRandomNumber($length)
    {
        $randomString = '';
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateReferenceID()
    {
        return "R" . date('d') . time() . strtoupper(\Str::random(5));
    }

    public static function customEncrypt($data)
    {
        $key = env('ENCRYPTION_KEY');
        $cipher = 'AES-256-CBC';
        $iv = Str::random(openssl_cipher_iv_length($cipher));
        $encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function padWithZeros($number, $totalLength)
    {
        // Convert the number to a string
        $numberStr = (string)$number;
        // Use str_pad to add leading zeros
        $paddedNumber = str_pad($numberStr, $totalLength, '0', STR_PAD_LEFT);
        return $paddedNumber;
    }

    public static function reversePaddedNumber($paddedNumber)
    {
        // Convert the padded number string to an integer
        $number = (int)$paddedNumber;
        return $number;
    }

    public static function encryptAES($data, $key)
    {
        $iv = openssl_random_pseudo_bytes(16);
        // Encrypt the data using AES-256-CBC
        $ciphertext = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        // Combine the IV and ciphertext for storage
        return base64_encode($iv . $ciphertext);
    }

    public static function decryptAES($ciphertext_base64, $key)
    {
        // Decode the base64 encoded data
        $ciphertext = base64_decode($ciphertext_base64);
        // Extract the IV and ciphertext
        $iv = substr($ciphertext, 0, 16);
        $ciphertext = substr($ciphertext, 16);
        // Decrypt the data using AES-256-CBC
        return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public static function upload_s3_image($file, $path, $old_file = null)
    {
        $file_name = time() . rand(0, 10000000000000) . pathinfo(rand(111111111111, 999999999999), PATHINFO_FILENAME);
        $file_name = $file_name . '.' . $file->getClientOriginalExtension();
        $file_path = "storage/$path";
        $file->move($file_path, $file_name);
        $file_path = "storage/$path/$file_name";
        // Storage::disk('s3')->put($file_path, file_get_contents($file->getRealPath()));
        return $file_path;
    }

    public static function upload_base64_s3_image($file, $path, $old_file = null)
    {
        if ($old_file) {
            deleteS3Image($old_file);
        }
        $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
        $file_name = time() . rand(0, 10000000000000) . pathinfo(rand(111111111111, 999999999999), PATHINFO_FILENAME);
        $file_name = $file_name . '.png';
        $file_path = "storage/$path/$file_name";
        Storage::disk('s3')->put($file_path, $file);
        return $file_path;
    }

    public static function generateIserveuToken()
    {

        $clint_id = env('ISU_CLIENT_ID');
        $clint_secret = env('ISU_CLIENT_SECRET');
        $token_key = env('ISU_TOKEN_KEY');

        $data = '{ "client_id": "' . $clint_id . '","client_secret": "' . $clint_secret . '","epoch": "' . time() . '"}';
        $iv = openssl_random_pseudo_bytes(16);
        $decodedKey = base64_decode($token_key);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
        $combined = $iv . $encrypted;
        return base64_encode($combined);
    }

    public static function encryptPidData($pidData, $key, $iv)
    {
        $ciphertext_raw = openssl_encrypt($pidData, 'AES-128-CBC', $key, $options = OPENSSL_RAW_DATA, $iv);
        $request1 = base64_encode($ciphertext_raw);
        return $request1;
    }
}

function generateCommonAgentID()
{
    $id = "P" . random_int(1000000, 9999999);
    $check = \DB::table('users')->where('cms_agent_id', $id)->count();
    if ($check > 0) {
        return generateCommonAgentID();
    }
    return $id;
}

function providerType($provider)
{
    if (auth()->check() && auth()->user()->role_id == 1) {
        switch ($provider) {
            case 1:
                return "Paysprint";
            case 2:
                return "BankIt";
            case 3:
                return "iServeU";
            case 4:
                return "Easebuzz";
            default:
                return "N/A";
        }
    } else {
        return "N/A";
    }
}

function aepsProvider($providerID, $company)
{
    $data = array();
    switch ($providerID) {
        case 17:
            $data['id'] = $company->dmt_provider;
            $data['provider_name'] = providerType($company->dmt_provider);
            return $data;
        case 19:
            $data['id'] = $company->aeps_provider;
            $data['provider_name'] = providerType($company->aeps_provider);
            return $data;
        case 25:
            $data['id'] = $company->cms_provider;
            $data['provider_name'] = providerType($company->cms_provider);
            return $data;
        default:
            $data['id'] = 0;
            $data['provider_name'] = "";
            return $data;
    }
}

function pre($data)
{
    echo "<pre>";
    print_r($data);
    exit;
}

function isMerchant()
{
    if (auth()->guard('merchant')->check()) {
        if (auth()->guard('merchant')->user()) {
            return true;
        }
    }
    return false;
}

function encryptPayoutResponse(string $responseBody, string $key): string
{
    $iv = openssl_random_pseudo_bytes(16);
    $decodedKey = base64_decode($key);
    $encrypted = openssl_encrypt($responseBody, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
    $combined = $iv . $encrypted;
    return base64_encode($combined);
}

function decryptPayoutResponse(string $encryptedData, string $key): string
{
    $decodedData = base64_decode($encryptedData);
    $decodedKey = base64_decode($key);
    $iv = substr($decodedData, 0, 16);
    $encryptedBody = substr($decodedData, 16);
    $decrypted = openssl_decrypt($encryptedBody, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

function tempgenerateReferenceIDT()
{
    return "R" . date('d') . time() . strtoupper(\Str::random(5));
}


function tempgenerateIserveuToken()
{

    $clint_id = env('ISU_CLIENT_ID');
    $clint_secret = env('ISU_CLIENT_SECRET');
    $token_key = env('ISU_TOKEN_KEY');

    $data = '{ "client_id": "' . $clint_id . '","client_secret": "' . $clint_secret . '","epoch": "' . time() . '"}';
    $iv = openssl_random_pseudo_bytes(16);
    $decodedKey = base64_decode($token_key);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $decodedKey, OPENSSL_RAW_DATA, $iv);
    $combined = $iv . $encrypted;
    return base64_encode($combined);
}

function getCredentials($id)
{
    $res = Credentials::find($id);
    $apiKey = $res->api_key;
    $saltKey = $res->salt_key;
    $id = $res->id;
    return [$id, $apiKey, $saltKey];
}

function getAdminLoginUser()
{
    if (auth()->check()) {
        return auth()->user();
    }
    return null;
}
function isSuperAdmin($user)
{
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    return false;
}
function base64Encode($value)
{
    return base64_encode(base64_encode($value));
}

function base64Decode($value)
{
    return base64_decode(base64_decode($value));
}

function hasAdminPermission($name)
{
    if (auth()->user()->role_id != 1) {
        return true;
    }
    if ($name) {
        if (auth()->user()?->can($name)) {
            return true;
        }
    }
    return false;
}
