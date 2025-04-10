<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $company_name }}</title>

    <style type="text/css">
        /* Client-specific Styles */
        #outlook a {padding:0;} /* Force Outlook to provide a "view in browser" menu link. */
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
        /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
        .ExternalClass {width:100%;} /* Force Hotmail to display emails at full width */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  */
        #backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
        img {outline:none; text-decoration:none;border:none; -ms-interpolation-mode: bicubic;}
        a img {border:none;}
        .image_fix {display:block;}
        p {margin: 0px 0px !important;}
        table td {border-collapse: collapse;}
        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
        a {color: #33b9ff;text-decoration: none;text-decoration:none!important;}
        /*STYLES*/
        table[class=full] { width: 100%; clear: both; }
        /*IPAD STYLES*/
        @media only screen and (max-width: 640px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: #33b9ff; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: #33b9ff !important;
                pointer-events: auto;
                cursor: default;
            }
            table[class=devicewidth] {width: 440px!important;text-align:center!important;}
            table[class=devicewidthinner] {width: 420px!important;text-align:center!important;}
            img[class=banner] {width: 440px!important;height:220px!important;}
            img[class=colimg2] {width: 440px!important;height:220px!important;}


        }
        /*IPHONE STYLES*/
        @media only screen and (max-width: 480px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: #ffffff; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: #ffffff !important;
                pointer-events: auto;
                cursor: default;
            }
            table[class=devicewidth] {width: 280px!important;text-align:center!important;}
            table[class=devicewidthinner] {width: 260px!important;text-align:center!important;}
            img[class=banner] {width: 280px!important;height:140px!important;}
            img[class=colimg2] {width: 280px!important;height:140px!important;}
            td[class="padding-top15"]{padding-top:15px!important;}


        }
    </style>
</head>
<body>
<!-- Start of preheader -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="preheader" >
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <!-- Spacing -->
                            <tr>
                                <td width="100%" height="10"></td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td>
                                    <table width="48%" align="left" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                        <tr>
                                            <td align="left" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 13px;color: #282828" st-content="viewonline">
                                                <a href="#" style="text-decoration: none; color: #333333">View Online </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <table width="48%" align="right" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                        <tr>
                                            <td align="right" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 13px;color: #282828" st-content="forward">
                                                <a href="#" style="text-decoration: none; color: #333333">Forward to Friend </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td width="100%" height="10"></td>
                            </tr>
                            <!-- Spacing -->
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of preheader -->
<!-- Start of header -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="header">
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table bgcolor="#FFF" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <!-- Spacing -->
                            <tr>
                                <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td>
                                    <!-- logo -->
                                    <table width="140" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                        <tbody>
                                        <tr>
                                            <td width="144" height="60" align="center">
                                                <div class="imgpop">
                                                    <a target="_blank" href="#">
                                                        <img src="{{ $company_logo }}" alt="" border="0" width="144" height="25" style="display:block; border:none; outline:none; text-decoration:none; margin-left: 15px; height: 50px;">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!-- end of logo -->
                                    <!-- start of menu -->
                                    <table width="250" border="0" align="right" valign="middle" cellpadding="0" cellspacing="0" border="0" class="devicewidth">
                                        <tbody>
                                        <tr>
                                            <td align="center" style="font-family: Helvetica, arial, sans-serif; font-size: 20px;color: #333" st-content="phone"  height="60">
                                                CALL: {{ $support_number }}
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!-- end of menu -->
                                </td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td height="5" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                            </tr>
                            <!-- Spacing -->
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of Header -->
<!-- Start of main-banner -->

<!-- End of main-banner -->
<!-- Start of seperator -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="seperator">
    <tbody>
    <tr>
        <td>
            <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth">
                <tbody>
                <tr>
                    <td align="center" height="30" style="font-size:1px; line-height:1px;">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of seperator -->
<!-- Start of heading -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="seperator">
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth">
                            <tbody>
                            <tr>
                                <td align="center" style="font-family: Helvetica, arial, sans-serif; font-size: 24px; color: #ffffff; padding: 15px 0;" st-content="heading" bgcolor="#3498db" align="center">
                                    Welcome to {{ $company_name }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of heading -->
<!-- 2columns -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <tr>
                                <td>
                                    <table width="290" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                        <tbody>
                                        <!-- Spacing -->
                                        <tr>
                                            <td width="100%" height="20"></td>
                                        </tr>
                                        <!-- Spacing -->
                                        <tr>
                                            <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                    <tbody>
                                                    <!-- image -->
                                                    <tr>

                                                    </tr>
                                                    <!-- title -->
                                                    <tr>

                                                    </tr>
                                                    <!-- end of title -->
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- end of text content table -->
                                        </tbody>
                                    </table>
                                    <!-- end of left column -->
                                    <!-- start of right column -->
                                    <table width="290" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                        <tbody>
                                        <!-- Spacing -->
                                        <tr>
                                            <td width="100%" height="20"></td>
                                        </tr>
                                        <!-- Spacing -->
                                        <tr>
                                            <td>
                                                <!-- start of text content table -->

                                            </td>
                                        </tr>
                                        <!-- end of text content table -->
                                        </tbody>
                                    </table>
                                    <!-- end of right column -->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- end of 2 columns -->
<!-- 2columns -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <tr>
                                <td>
                                    <table width="290" align="left" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                        <tbody>
                                        <!-- Spacing -->
                                        <tr>
                                            <td width="100%" height="10"></td>
                                        </tr>
                                        <!-- Spacing -->
                                        <tr>
                                            <td>
                                                <!-- start of text content table -->
                                                <table width="270" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidth">
                                                    <tbody>
                                                    <!-- image -->

                                                    <!-- title -->
                                                    <tr>

                                                    </tr>
                                                    <!-- end of title -->
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- end of text content table -->
                                        <!-- Spacing -->

                                        <!-- Spacing -->
                                        </tbody>
                                    </table>
                                    <!-- end of left column -->

                                    <!-- end of right column -->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- end of 2 columns -->
<!-- Start of seperator -->

<!-- article -->

<!-- end of article -->
<!-- article -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <!-- Spacing -->

                            <!-- Spacing -->
                            <tr>
                                <td>
                                    <table width="560" align="center" border="0" cellpadding="0" cellspacing="0" class="devicewidthinner">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <!-- start of text content table -->

                                                <!-- start of right column -->
                                                <table width="100%" align="right" border="0" cellpadding="0" cellspacing="0" class="devicewidthinner">
                                                    <tbody>
                                                    <!-- title -->
                                                    <tr>
                                                        <td style="font-family: Helvetica, arial, sans-serif; font-size: 18px; color: #262626; text-align:left; line-height: 20px;" class="padding-top15">
                                                            Dear, {{ $name }},
                                                        </td>
                                                    </tr>
                                                    <!-- end of title -->
                                                    <!-- Spacing -->
                                                    <tr>
                                                        <td width="100%" height="10"></td>
                                                    </tr>
                                                    <!-- Spacing -->
                                                    <!-- content -->
                                                    <tr>
                                                        <td style="font-family: Helvetica, arial, sans-serif; font-size: 14px; color: #4f5458; text-align:left; line-height: 20px;">
                                                            {!! $content !!}
                                                        </td>
                                                    </tr>

                                                    <!-- end of content -->
                                                    </tbody>
                                                </table>
                                                <!-- end of right column -->
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td height="20"></td>
                            </tr>
                            <!-- Spacing -->
                            <!-- bottom-border -->
                            <tr>
                                <table cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        <td width="1000" bgcolor="#3498db"></td>
                                        <td height="2" bgcolor="#EEE" style="font-size:1px; line-height:1px; mso-line-height-rule: exactly;">&nbsp;</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </tr>
                            <!-- /bottom-border -->
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- end of article -->
<!-- Start of seperator -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="seperator">
    <tbody>
    <tr>
        <td>
            <table width="600" align="center" cellspacing="0" cellpadding="0" border="0" class="devicewidth">
                <tbody>
                <tr>
                    <td align="center" height="30" style="font-size:1px; line-height:1px;">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of seperator -->
<!-- footer -->

<!-- end of footer -->
<!-- Start of Postfooter -->
<table width="100%" bgcolor="#EEE" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" st-sortable="postfooter" >
    <tbody>
    <tr>
        <td>
            <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                <tbody>
                <tr>
                    <td width="100%">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                            <tbody>
                            <!-- Spacing -->
                            <tr>
                                <td width="100%" height="20"></td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td align="center" valign="middle" style="font-family: Helvetica, arial, sans-serif; font-size: 13px;color: #333333" st-content="preheader">
                                    Don't want to receive email Updates? <a href="#" style="text-decoration: none; color: #3498db">Unsubscribe here </a>
                                </td>
                            </tr>
                            <!-- Spacing -->
                            <tr>
                                <td width="100%" height="20"></td>
                            </tr>
                            <!-- Spacing -->
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<!-- End of postfooter -->
</body>
</html>
