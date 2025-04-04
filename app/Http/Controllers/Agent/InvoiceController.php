<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Report;
use App\Models\Provider;
use App\Models\User;
use App\Models\Company;
use App\Models\Mreport;
use App\Models\Beneficiary;
use \Crypt;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Library\SmsLibrary;
use Helpers;
use App\Models\WhatsappReportUrl;
class InvoiceController extends Controller
{


    function transaction_receipt($id)
    {
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
        if ($reports) {
            $total_amount = Report::where('id', $report_id)->sum('amount');
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();

            $beneficiary_id = $reports->beneficiary_id;

            if ($beneficiary_id) {
                $beneficiary = Beneficiary::find($beneficiary_id);
            }
            $data = array(
                'id' => $id,
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,

                // beneficiary details
                'beneficiary_name' => $beneficiary->name ?? 'NA',
                'account_number' => $beneficiary->account_number ?? 'NA',
                'bank_name' => $beneficiary->bank_name ?? "NA",
                'ifsc' => $beneficiary->ifsc ?? "NA",
                'remiter_name' => $beneficiary->remiter_name??"NA",
                'remiter_number' => $beneficiary->remiter_number??"NA",
                'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                'full_amount' => $total_amount,
            );
            return view('agent.invoice.transaction_receipt', compact('reports'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function mobile_receipt($id)
    {
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
        if ($reports) {
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $data = array(
                'id' => $id,
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,
            );
            return view('agent.invoice.mobile_receipt')->with($data);
        } else {
            return Redirect::back();
        }
    }

    function money_receipt($mreportid)
    {
        $mreport = Mreport::find($mreportid);
        if ($mreportid) {
            $reports = Report::where('mreportid', $mreportid)->first();
            $total_amount = Report::where('mreportid', $mreportid)->sum('amount');
            $userdetails = User::find($mreport->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $beneficiary_id = $reports->beneficiary_id;
            $beneficiary = Beneficiary::find($beneficiary_id);
            $data = array(
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,
                'created_at' => "$reports->created_at",
                'total_amount' => number_format($total_amount, 2),
                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                // beneficiary details
                'beneficiary_name' => $beneficiary->name,
                'account_number' => $beneficiary->account_number,
                'bank_name' => $beneficiary->bank_name,
                'ifsc' => $beneficiary->ifsc,
                'remiter_name' => $beneficiary->remiter_name,
                'remiter_number' => $beneficiary->remiter_number,
                'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                'full_amount' => number_format($total_amount, 2),
                'id' =>$mreportid
            );
            $reports = Report::where('mreportid', $mreportid)->get();
            // return view('agent.invoice.money_receipt', compact('reports'))->with($data);
            return view('agent.invoice.money_receipt', compact('reports'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function thermal_printer_receipt($mreportid)
    {
        $mreport = Mreport::find($mreportid);
        if ($mreportid) {
            $reports = Report::where('mreportid', $mreportid)->first();
            $total_amount = Report::where('mreportid', $mreportid)->sum('amount');
            $userdetails = User::find($mreport->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $beneficiary_id = $reports->beneficiary_id;
            $beneficiary = Beneficiary::find($beneficiary_id);

            $reportsdata = Report::where('mreportid', $mreport->id)->first();
            $data = array(
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,
                'created_at' => "$reports->created_at",
                'total_amount' => number_format($total_amount, 2),
                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,
                'service_name' => $reportsdata->provider->service->service_name,

                // beneficiary details
                'beneficiary_name' => $beneficiary->name,
                'account_number' => $beneficiary->account_number,
                'bank_name' => $beneficiary->bank_name,
                'ifsc' => $beneficiary->ifsc,
                'remiter_name' => $beneficiary->remiter_name,
                'remiter_number' => $beneficiary->remiter_number,
                'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                'full_amount' => number_format($total_amount, 2),
            );
            $reports = Report::where('mreportid', $mreportid)->get();
            return view('agent.invoice.money_mobile_receipt', compact('reports'))->with($data);
        } else {
            return Redirect::back();
        }
    }


    function invoice()
    {
        return view('agent.invoice.gst_invoice');
    }

    public function downloadReceiptSendWhatsapp(Request $request)
    {
        // dd($request->id);
        $rules = array(
            'mobile_number' => 'required',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $generateRandomNumber = Helpers::generateRandomNumber(10);

        $generateRandomNumberWithTime = time() . $generateRandomNumber;
        $whatsapp_url = url('web-receipt/' . $request->id);
        $whatsappReportUrlData = WhatsappReportUrl::where('report_id', Crypt::decrypt($request->id))->first();
        // dd(empty($whatsappReportUrlData));
        if (!empty($whatsappReportUrlData)) {
            WhatsappReportUrl::where('report_id', Crypt::decrypt($request->id))->update(['whatsapp_web_url' => $whatsapp_url]);
            $generateRandomNumberWithTime = $whatsappReportUrlData->number;
        } else {

            $whatsappUrlData["number"] = $generateRandomNumberWithTime;
            $whatsappUrlData["report_id"] = Crypt::decrypt($request->id);
            $whatsappUrlData["whatsapp_web_url"] = $whatsapp_url;
            WhatsappReportUrl::create($whatsappUrlData);
        }


        $url = url('web-receipt/' . $generateRandomNumberWithTime);
        $message = "Please click the link below to download your invoice. $url";
        $template_id = 24;
        $whatsappArr = [$url];
        $library = new SmsLibrary();
        $library->send_sms($request->mobile_number, $message, $template_id, $whatsappArr);

        return Response()->json(['status' => 'success', 'message' => 'Message Send Successfully']);


    }


    public function downloadTransactionReceipt($id, Request $request)
    {

        $whatsappReportData = WhatsappReportUrl::where('number', $id)->first();
        if ($whatsappReportData) {

            $report_id = $whatsappReportData->report_id;
            $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
            if ($reports) {
                $total_amount = Report::where('id', $report_id)->sum('amount');
                $userdetails = User::find($reports->user_id);
                $company_id = $userdetails->company_id;
                $company = Company::where('id', $company_id)->first();

                $beneficiary_id = $reports->beneficiary_id;

                if ($beneficiary_id) {
                    $beneficiary = Beneficiary::find($beneficiary_id);
                }
                $data = array(
                    'page_title' => 'Receipt',
                    'company_name' => $company->company_name,
                    'company_email' => $company->company_email,
                    'support_number' => $company->support_number,
                    'company_address' => $company->company_address,
                    'company_website' => $company->company_website,

                    'agent_name' => $userdetails->member->shop_name,
                    'agent_email' => $userdetails->email,
                    'agent_number' => $userdetails->mobile,
                    'office_address' => $userdetails->member->office_address,

                    'report_id' => $report_id,
                    'created_at' => "$reports->created_at",
                    'provider_name' => $reports->provider->provider_name,
                    'number' => $reports->number,
                    'txnid' => $reports->txnid,
                    'amount' => number_format($reports->amount, 2),
                    'status' => $reports->status->status,

                    // beneficiary details
                    'beneficiary_name' => $beneficiary->name ?? 'NA',
                    'account_number' => $beneficiary->account_number ?? 'NA',
                    'bank_name' => $beneficiary->bank_name ?? "NA",
                    'ifsc' => $beneficiary->ifsc ?? "NA",
                    'remiter_name' => $beneficiary->remiter_name ?? "NA",
                    'remiter_number' => $beneficiary->remiter_number ?? "NA",
                    'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                    'full_amount' => $total_amount,
                );
                view()->share('data', $data);
                view()->share('reports', $reports);

                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->setBasePath(public_path());
                $dompdf->loadHtml(view('agent.invoice.pdf_transaction_receipt'));
                $dompdf->setPaper([0, 0, 800.98, 700.85], 'landscape');
                $dompdf->render();
                // $dompdf->stream("trustxpay.pdf",array("Attachment" => false));
                $dompdf->stream("trustxpay.pdf");
            }
        }

    }

    public function mobileReceiptSendWhatsapp(Request $request)
    {
        // dd($request->id);
        $rules = array(
            'mobile_number' => 'required',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $generateRandomNumber = Helpers::generateRandomNumber(10);

        $generateRandomNumberWithTime = time() . $generateRandomNumber;
        $whatsapp_url = url('mobile-receipt/' . $request->id);
        $whatsappReportUrlData = WhatsappReportUrl::where('report_id', Crypt::decrypt($request->id))->first();
        // dd(empty($whatsappReportUrlData));
        if (!empty($whatsappReportUrlData)) {
            WhatsappReportUrl::where('report_id', Crypt::decrypt($request->id))->update(['whatsapp_mobile_url' => $whatsapp_url]);
            $generateRandomNumberWithTime = $whatsappReportUrlData->number;
        } else {

            $whatsappUrlData["number"] = $generateRandomNumberWithTime;
            $whatsappUrlData["report_id"] = Crypt::decrypt($request->id);
            $whatsappUrlData["whatsapp_mobile_url"] = $whatsapp_url;
            WhatsappReportUrl::create($whatsappUrlData);
        }


        $url = url('mobile-receipt/' . $generateRandomNumberWithTime);
        $message = "Please click the link below to download your invoice. $url";
        $template_id = 24;
        $whatsappArr = [$url];
        $library = new SmsLibrary();
        $library->send_sms($request->mobile_number, $message, $template_id, $whatsappArr);

        return Response()->json(['status' => 'success', 'message' => 'Message Send Successfully']);


    }

    public function downloadMobileReceipt($id, Request $request)
    {

        $whatsappReportData = WhatsappReportUrl::where('number', $id)->first();
        if ($whatsappReportData) {

            $report_id = $whatsappReportData->report_id;
            $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
            if ($reports) {
                $total_amount = Report::where('id', $report_id)->sum('amount');
                $userdetails = User::find($reports->user_id);
                $company_id = $userdetails->company_id;
                $company = Company::where('id', $company_id)->first();

                $beneficiary_id = $reports->beneficiary_id;

                if ($beneficiary_id) {
                    $beneficiary = Beneficiary::find($beneficiary_id);
                }
                $data = array(
                    'page_title' => 'Receipt',
                    'company_name' => $company->company_name,
                    'company_email' => $company->company_email,
                    'support_number' => $company->support_number,
                    'company_address' => $company->company_address,
                    'company_website' => $company->company_website,

                    'agent_name' => $userdetails->member->shop_name,
                    'agent_email' => $userdetails->email,
                    'agent_number' => $userdetails->mobile,
                    'office_address' => $userdetails->member->office_address,

                    'report_id' => $report_id,
                    'created_at' => "$reports->created_at",
                    'provider_name' => $reports->provider->provider_name,
                    'number' => $reports->number,
                    'txnid' => $reports->txnid,
                    'amount' => number_format($reports->amount, 2),
                    'status' => $reports->status->status,

                    // beneficiary details
                    'beneficiary_name' => $beneficiary->name ?? 'NA',
                    'account_number' => $beneficiary->account_number ?? 'NA',
                    'bank_name' => $beneficiary->bank_name ?? "NA",
                    'ifsc' => $beneficiary->ifsc ?? "NA",
                    'remiter_name' => $beneficiary->remiter_name ?? "NA",
                    'remiter_number' => $beneficiary->remiter_number ?? "NA",
                    'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                    'full_amount' => $total_amount,
                );
                // dd($data);

                // return view('agent.invoice.transaction_receipt', compact('reports'))->with($data);
                view()->share('data', $data);
                view()->share('reports', $reports);

                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->setBasePath(public_path());
                $dompdf->loadHtml(view('agent.invoice.pdf_mobile_receipt'));
                $dompdf->setPaper([0, 0, 800.98, 700.85], 'landscape');
                $dompdf->render();
                // $dompdf->stream("trustxpay.pdf",array("Attachment" => false));
                $dompdf->stream("trustxpay.pdf");
            }
        }

    }

    public function moneyReceiptWhatsappMsg(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mreport = Mreport::find($request->id);
        if ($mreport) {
            $reports = Report::where('mreportid', $request->id)->first();


            $generateRandomNumber = Helpers::generateRandomNumber(10);

            $generateRandomNumberWithTime = time() . $generateRandomNumber;
            $whatsapp_url = url('moneyReceipt/' . $request->id);
            $whatsappReportUrlData = WhatsappReportUrl::where('report_id', $reports->id)->first();
            // dd(empty($whatsappReportUrlData));
            if (!empty($whatsappReportUrlData)) {
                WhatsappReportUrl::where('report_id', $reports->id)->update(['whatsapp_mobile_url' => $whatsapp_url]);
                $generateRandomNumberWithTime = $whatsappReportUrlData->number;
            } else {

                $whatsappUrlData["number"] = $generateRandomNumberWithTime;
                $whatsappUrlData["report_id"] = $reports->id;
                $whatsappUrlData["whatsapp_mobile_url"] = $whatsapp_url;
                WhatsappReportUrl::create($whatsappUrlData);
            }


            $url = url('moneyReceipt/' . $generateRandomNumberWithTime);
            $message = "Please click the link below to download your invoice. $url";
            $template_id = 24;
            $whatsappArr = [$url];
            $library = new SmsLibrary();
            $library->send_sms($request->mobile_number, $message, $template_id, $whatsappArr);
        }

        return Response()->json(['status' => 'success', 'message' => 'Message Send Successfully']);

    }

    public function downloadMoneyReceipt($id, Request $request)
    {

        $whatsappReportData = WhatsappReportUrl::where('number', $id)->first();
        if ($whatsappReportData) {

            $report_id = $whatsappReportData->report_id;
            $reports = Report::where('id', $report_id)->first();

            if ($reports) {
                $mreport = Mreport::find($reports->mreportid);
                if ($mreport) {


                    $reports = Report::where('mreportid', $reports->mreportid)->first();
                    $total_amount = Report::where('mreportid', $reports->mreportid)->sum('amount');
                    $userdetails = User::find($mreport->user_id);
                    $company_id = $userdetails->company_id;
                    $company = Company::where('id', $company_id)->first();
                    $beneficiary_id = $reports->beneficiary_id;
                    $beneficiary = Beneficiary::find($beneficiary_id);
                    $data = array(
                        'page_title' => 'Receipt',
                        'company_name' => $company->company_name,
                        'company_email' => $company->company_email,
                        'support_number' => $company->support_number,
                        'company_address' => $company->company_address,
                        'company_website' => $company->company_website,
                        'created_at' => "$reports->created_at",
                        'total_amount' => number_format($total_amount, 2),
                        'agent_name' => $userdetails->member->shop_name,
                        'agent_email' => $userdetails->email,
                        'agent_number' => $userdetails->mobile,
                        'office_address' => $userdetails->member->office_address,

                        // beneficiary details
                        'beneficiary_name' => $beneficiary->name,
                        'account_number' => $beneficiary->account_number,
                        'bank_name' => $beneficiary->bank_name,
                        'ifsc' => $beneficiary->ifsc,
                        'remiter_name' => $beneficiary->remiter_name,
                        'remiter_number' => $beneficiary->remiter_number,
                        'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                        'full_amount' => number_format($total_amount, 2),
                        'id' => $reports->mreportid
                    );
                    $reports = Report::where('mreportid', $reports->mreportid)->get();

                    // return view('agent.invoice.transaction_receipt', compact('reports'))->with($data);
                    view()->share('data', $data);
                    view()->share('reports', $reports);

                    $options = new Options();
                    $options->setIsRemoteEnabled(true);
                    $dompdf = new Dompdf($options);
                    $dompdf->setBasePath(public_path());
                    $dompdf->loadHtml(view('agent.invoice.pdf_money_receipt'));
                    $dompdf->setPaper([0, 0, 800.98, 700.85], 'landscape');
                    $dompdf->render();
                    // $dompdf->stream("trustxpay.pdf",array("Attachment" => false));
                    $dompdf->stream("trustxpay.pdf");
                }
            }
        }

    }

    function cmsTransactionReceipt($id)
    {
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
        if ($reports) {
            $total_amount = Report::where('id', $report_id)->sum('amount');
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();

            $beneficiary_id = $reports->beneficiary_id;

            if ($beneficiary_id) {
                $beneficiary = Beneficiary::find($beneficiary_id);
            }
            $data = array(
                'id' => $id,
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,
                'payid' => $reports->payid,

                // beneficiary details
                'beneficiary_name' => $beneficiary->name ?? 'NA',
                'account_number' => $beneficiary->account_number ?? 'NA',
                'bank_name' => $beneficiary->bank_name ?? "NA",
                'ifsc' => $beneficiary->ifsc ?? "NA",
                'remiter_name' => $beneficiary->remiter_name ?? "NA",
                'remiter_number' => $beneficiary->remiter_number ?? "NA",
                'channel' => ($reports->channel == 2) ? 'IMPS' : 'NEFT',
                'full_amount' => $total_amount,
            );
            return view('agent.invoice.cms-transaction_receipt', compact('reports'))->with($data);
        } else {
            return Redirect::back();
        }
    }

    function cmsMobileReceipt($id)
    {
        $report_id = Crypt::decrypt($id);
        $reports = Report::where('id', $report_id)->whereIn('status_id', [1, 2, 3, 5, 6])->first();
        if ($reports) {
            $userdetails = User::find($reports->user_id);
            $company_id = $userdetails->company_id;
            $company = Company::where('id', $company_id)->first();
            $data = array(
                'id' => $id,
                'page_title' => 'Receipt',
                'company_name' => $company->company_name,
                'company_email' => $company->company_email,
                'support_number' => $company->support_number,
                'company_address' => $company->company_address,
                'company_website' => $company->company_website,

                'agent_name' => $userdetails->member->shop_name,
                'agent_email' => $userdetails->email,
                'agent_number' => $userdetails->mobile,
                'office_address' => $userdetails->member->office_address,

                'report_id' => $report_id,
                'created_at' => "$reports->created_at",
                'provider_name' => $reports->provider->provider_name,
                'number' => $reports->number,
                'txnid' => $reports->txnid,
                'amount' => number_format($reports->amount, 2),
                'status' => $reports->status->status,
                'payid' => $reports->payid,
            );
            return view('agent.invoice.cms-mobile_receipt')->with($data);
        } else {
            return Redirect::back();
        }
    }
}
