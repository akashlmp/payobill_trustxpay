<?php

namespace App\library {
    use App\Models\User;
    use App\Models\Company;


    class CompanyLibrary {



        public function get_company_detail(){
            if (!empty($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            } else {
                $host = "localhost:8888";
            }
            $company = Company::where('company_website', $host)->where('status_id', 1)->first();
            if ($company) {
                return $company;
            } else {
                return $company;
            }
        }

        public static function company_details()
        {
            $website = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'newlaravel.dev';
            if ($website) {
                return Company::where('company_website', $website)->firstOrFail();
            }
        }

    }

}