<?php

    namespace XakepSoft\Banking;

    class HabaLV
    {
        static  $url = 'https://ib.swedbank.lv';
        private $login = '';
        private $pswrd = '';
        private $curl = NULL; 
        private $cookies = '';
        private $captcha = 0;
        private $codes = array();
        public static $CURRs = array
        (
            'EUR'=>'EUR', 'AUD'=>'AUD', 'CAD'=>'CAD', 'CHF'=>'CHF', 'CZK'=>'CZK', 'DKK'=>'DKK', 'GBP'=>'GBP', 'HUF'=>'HUF', 
            'JPY'=>'JPY', 'LTL'=>'LTL', 'NOK'=>'NOK', 'PLN'=>'PLN', 'RUB'=>'RUB', 'SEK'=>'SEK', 'SGD'=>'SGD', 'USD'=>'USD'
        );
        const HABA_BIC = 'HABALV22';
        private static $BICs = array
        (
            'AIZK'=>'AIZKLV22' , 'CBBR'=>'CBBRLV22' , 'BLIB'=>'BLIBLV22' , 'SECT'=>'SECTLV21' , 'KKSD'=>'KKSDLV21' ,
            'BATR'=>'BATRLV2X' , 'HABA'=>'HABALV22' , 'VBRI'=>'VBRILV2X' , 'SPRA'=>'SPRALV21' , 'LFIK'=>'LFIKLV21' ,
            'LATB'=>'LATBLV22' , 'LACB'=>'LACBLV2X' , 'MOSB'=>'MOSBLV2X' , 'TAPS'=>'TAPSLV21' , 'LCDE'=>'LCDELV22' ,
            'LHZB'=>'LHZBLV22' , 'UBAL'=>'UBALLV2X' , 'LATC'=>'LATCLV22' , 'ABCR'=>'ABCRLV21' , 'LIBC'=>'LIBCLV21' ,
            'MULT'=>'MULTLV2X' , 'NDEA'=>'NDEALV2X' , 'RIKO'=>'RIKOLV2X' , 'BLPB'=>'BLPBLV21' , 'RGNS'=>'RGNSLV22' ,
            'OKBA'=>'OKBALV21' , 'PARX'=>'PARXLV22' , 'PRTT'=>'PRTTLV22' , 'EVSE'=>'EVSELV21' , 'LLBB'=>'LLBBLV2X' ,
            'RTMB'=>'RTMBLV2X' , 'MARA'=>'MARALV22' , 'UNLA'=>'UNLALV2X' , 'FSEC'=>'FSECLV21' , 'KRAJ'=>'KRAJLV22' ,
            'KBRB'=>'KBRBLV2X' , 'VEFB'=>'VEFBLV22' , 'TREL'=>'TRELLV22' , 'GOSB'=>'GOSBLV21' , 
            'LAPB'=>'LAPBLV2X' , 'SNOR'=>'SNORLV22' , 'OKOY'=>'OKOYLV21' , 'LPNS'=>'LPNSLV21' ,
            'RIBR'=>'RIBRLV22' , 'HAND'=>'HANDLV22' , 'XRIS'=>'XRISLV21' , 'JSSI'=>'JSSILV21'
        );

        private $j_session = '';
        private $post_data = array();

        private $error_line = '';
        private $error_code = '';
        private $error_text = '';
        private $error_time = '';
        private $errors = array();

        private $accounts = array();
        private $logged_on = FALSE;
        private $persist = FALSE;

        const
                // System error codes
                ERR_SYS = 100001, ERR_READ_FORM = 100002, ERR_OCR = 100003, ERR_HTTP_POST = 100004, ERR_HTTP_GET = 100005, ERR_PARSE_ACC = 100006,
                ERR_FIND_ACC = 100007, ERR_PARSE_FORM = 100008, ERR_FIND_CAPTCHA = 100009, ERR_READ_TSTATUS = 100010, ERR_READ_ACC = 100011,
                // User error codes
                ERR_LOGGED_ON = 200001, ERR_PASS_REQ = 200002, ERR_BIC_RCGNZ = 200003, ERR_REQ_PARAMS = 200004, ERR_EMPTY_PARAMS = 200005,
                ERR_IDENTIFY_BANK = 200006, ERR_INVALID_CURR = 200007, ERR_INVALID_ACC = 200008, ERR_INVALID_SRC = 200009, ERR_INVALID_DST = 200010,
                ERR_INVALID_BFTYPE = 200011, ERR_REQ_REGNUM = 200012, ERR_BAD_CODES = 200013;

        static private $error_messages = array(
            self::ERR_SYS => 'Internal system error' ,
            self::ERR_READ_FORM => 'Could not read HTML form data' ,
            self::ERR_OCR => 'OCR text recognition error' ,
            self::ERR_HTTP_POST => 'HTTP POST error' ,
            self::ERR_HTTP_GET => 'HTTP GET error' ,
            self::ERR_PARSE_ACC => 'Unable to parse account table',
            self::ERR_FIND_ACC => 'Unable to find account table' ,
            self::ERR_PARSE_FORM => 'Unable to parse HTML form' ,
            self::ERR_FIND_CAPTCHA => 'Unable to find captcha' ,
            self::ERR_READ_TSTATUS => 'Unable to determine the status of the transaction or operation' ,
            self::ERR_READ_ACC => 'Unable to retrieve account information' ,

            self::ERR_LOGGED_ON => 'Not logged on' ,
            self::ERR_PASS_REQ => 'Password change required' ,
            self::ERR_BIC_RCGNZ => 'Account number BIC code validation error' ,
            self::ERR_REQ_PARAMS => 'Mandatory parameters are missing' ,
            self::ERR_EMPTY_PARAMS => 'Mandatory parameters are empty' ,
            self::ERR_IDENTIFY_BANK => 'Unable to identify destination bank' ,
            self::ERR_INVALID_CURR => 'Invalid or unsupported currency code' ,
            self::ERR_INVALID_ACC => 'Invalid account number' ,
            self::ERR_INVALID_SRC => 'Invalid source account number' ,
            self::ERR_INVALID_DST => 'Invalid destination account number' ,
            self::ERR_INVALID_BFTYPE => 'Invalid beneficiary type' ,
            self::ERR_REQ_REGNUM => 'Person identification number or company registration number required',
            self::ERR_BAD_CODES => 'Code card data invalid or not provided'
        );

        function __construct( $codes, $session_name = '' )
        {
            if( !is_array($codes) || count($codes) < 24 ) trigger_error( self::$error_messages[self::ERR_BAD_CODES], E_USER_WARNING);
            $this->codes = $codes;
            if( !empty( $session_name ) )
            {
                $this->persist = TRUE; 
                $this->cookies = './' . md5( $session_name ); 
                if( is_file( $this->cookies . '.dat' ) )
                {
                    $lines = file( $this->cookies . '.dat', FILE_IGNORE_NEW_LINES);
                    $this->j_session = $lines[0];
                    $this->post_data = unserialize( $lines[1] );
                    $this->logged_on = TRUE;
                }
            }
            else $this->cookies = tempnam( self::get_temp_dir(), 'habalv_cookies_' );
            $this->curl = curl_init();
            curl_setopt( $this->curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt( $this->curl, CURLOPT_VERBOSE, 0 );
            curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER,1 );
            curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt( $this->curl, CURLOPT_TIMEOUT, 15 );
            curl_setopt( $this->curl, CURLOPT_COOKIEJAR,  $this->cookies );
            curl_setopt( $this->curl, CURLOPT_COOKIEFILE, $this->cookies );
        }

        function __destruct()
        {
            curl_close( $this->curl );
            if(!$this->persist) @unlink( $this->cookies );
        }

        public function login( $u, $p, $allow_password_refresh = FALSE )
        {
            $this->login = $u; $this->pswrd = $p;

            if( !is_array($this->codes) || count($this->codes) < 24 ) return $this->error( self::ERR_BAD_CODES , __LINE__);

            if(!$html = $this->read_from_url( self::$url . '/private' )) return $this->error( self::ERR_SYS , __LINE__);

            if(!$form = $this->parse_form( $html, 'loginForm' )) return $this->error( self::ERR_READ_FORM, __LINE__);

            if( preg_match( "/authPwdName:\s*\'(.*)\',/Usiu" , $html, $tmp ) > 0 ) $form['fields'][] = array( $tmp[1] , '' , 'authPwd' );
            else return $this->error( self::ERR_SYS , __LINE__);

            if(!$html = $this->post_to_url(self::$url.'/private/d2d/start', $this->prepare_post_data( $form['fields'] ) ))
                return $this->error( self::ERR_SYS, __LINE__);

            if( FALSE !== $error = self::parse_error( $html )) return $this->error( $error, __LINE__);

            if( preg_match( "/<label\s*for=\"challengeResponse\">\s*<img[^>]*src=\"(.*)\"/Usiu" , $html, $tmp ) > 0 )
            {
                file_put_contents( $tmpfname = tempnam( self::get_temp_dir(), 'gif_' ) , $this->read_from_url( self::$url . $tmp[1] ) , FILE_APPEND );
                if(!$this->captcha = (int)self::read_captcha( $tmpfname  ))return $this->error( self::ERR_OCR, __LINE__);
                @unlink( $tmpfname );
            }
            else return $this->error( self::ERR_SYS ,__LINE__); // Can't find captcha image

            if(!$form = $this->parse_form( $html, 'loginForm' )) return $this->error( self::ERR_READ_FORM, __LINE__);

            if(!$html = $this->post_to_url($form['action'],$this->prepare_post_data($form['fields'])))return $this->error( self::ERR_SYS, __LINE__);

            if(!$form = $this->parse_form( $html, 'navForm' )) return $this->error( self::ERR_READ_FORM, __LINE__);

            $this->j_session = substr( $form['action'] , strpos( $form['action'] , 'jsessionid=' ));
            $this->post_data = $this->prepare_post_data( $form['fields'] , array( 'pageId'=>'' ) );
            $this->logged_on = TRUE;
            if( $this->persist )file_put_contents( $this->cookies.'.dat' , $this->j_session . "\n" . serialize($this->post_data) );
            if( preg_match( "/<form[^>]*mainForm[^>]*>.*<div[^>]*class=\"warningMsg\"[^>]*>([^>]*)<\/div/Usiu" , $html , $tmp ) > 0 )
                if( strlen( trim( strip_tags( $tmp[1] ) ) ) >0 )
                {
                    if( $allow_password_refresh ) { $this->change_password( '!'.$p , FALSE ); $this->change_password( $p ); }
                    else { $this->logout(); return $this->error( self::ERR_PASS_REQ , __LINE__); }
                }
            return TRUE;
        }

        public function is_session_alive()
        {
            if(!$html = $this->read_from_url( self::$url . '/private;'.$this->j_session )) return $this->error( self::ERR_SYS ,__LINE__);
            if( preg_match( "/<form[^>]*\"loginForm\"[^>]*>.*id=\"userId\"/Usiu" , $html, $tmp ) > 0 ) return $this->logged_on = FALSE;
            return TRUE;
        }

        public function get_bic_code( $account )
        {
            $idx = substr( trim(strtoupper( $account )) , 4, 4 );
            if( !isset( self::$BICs[$idx] ) ) return $this->error( self::ERR_BIC_RCGNZ , __LINE__);
            return self::$BICs[$idx];
        }

        public function transfer_funds_local( $p )
        {
            if( !$this->logged_on ) return $this->error( self::ERR_LOGGED_ON, __LINE__ );

            if( !isset($p['src_acc']) || !isset($p['dst_acc']) || !isset($p['benefic']) || !isset($p['details']) || !isset($p['amount']) )
                return $this->error( self::ERR_REQ_PARAMS ,__LINE__ );

            if( ''==trim($p['src_acc']) || ''==trim($p['dst_acc']) || ''==trim($p['benefic']) || ''==trim($p['details']) || 0 >= $p['amount'] )
                return $this->error( self::ERR_EMPTY_PARAMS ,__LINE__ );

            if(!$bic = $this->get_bic_code( $p['dst_acc'] )) return $this->error( self::ERR_IDENTIFY_BANK  ,__LINE__);

            if( isset( $p['reg_type'] ) && '' !== trim( $p['reg_type'] ) )
            {
                $reg_type = strtoupper( trim( $p['reg_type'] ) );
                if( 'PRIVATE' == $reg_type ) $id_type = 'NATIONAL_ID_NUMBER';
                else if( 'ORGANISATION' == $reg_type ) $id_type = 'TAX_ID_NUMBER_V4';
                else return $this->error( self::ERR_INVALID_BFTYPE ,__LINE__);
                if( !isset($p['reg_num']) || ''==trim( $p['reg_num'] ))
                    return $this->error( self::ERR_REQ_REGNUM ,__LINE__);
                $reg_num = trim( $p['reg_num'] );
            }
            else { $reg_type = 'NONE'; $reg_num = ''; $id_type = ''; }

            if( isset($p['urgent']) && $p['urgent'] ) $urgent = 'true'; else $urgent = 'false';
            if( self::HABA_BIC == $bic ) $urgent = 'false';

            if( isset($p['currency']) && '' !== trim( $p['currency'] ) )
            {
                $p['currency'] = strtoupper( trim( $p['currency'] ) );
                if( isset( self::$CURRs[ $p['currency'] ] ) ) $currency = self::$CURRs[ $p['currency'] ];
                else return $this->error( self::ERR_INVALID_CURR , __LINE__ );
            } else $currency = 'EUR';

            if( empty( $this->get_accounts()) ) return $this->error( self::ERR_READ_ACC ,__LINE__);
            @$account = $this->accounts[ $p['src_acc'] ]['account'] OR $account = '';
            if('' == $account) return $this->error( self::ERR_INVALID_SRC ,__LINE__);

            if(!$html = $this->post_to_url( self::$url.'/private/d2d/payments2/domestic;'.$this->j_session , $this->post_data ))
                return $this->error( self::ERR_SYS, __LINE__ );

            if(!$form = $this->parse_form( $html, 'mainForm' )) return $this->error( self::ERR_READ_FORM , __LINE__);
            $post_data = $this->prepare_post_data( $form['fields'] , array(
                    'field1'=>'changeRemitterAccount','value1'=>'1','timestamp'=>time().rand(100,555),'account'=>$account,'definedPaymentId'=>'null',
                    'payment.urgent'=>$urgent,'payment.beneficiaryName'=>$p['benefic'],'payment.beneficiaryBankCode'=>$bic,'detailsChooser'=>'details',
                    'payment.creditor.partyType'=>$reg_type,'payment.creditor.idType'=>$id_type,'payment.creditor.identificationNumber'=>$reg_num,
                    'payment.currency'=>$currency,'payment.details'=>trim($p['details']),'payment.creditorReferenceNumber'=>'','payment.bopCode'=>'',
                    'payment.beneficiaryResidenceCountry'=>'LV','payment.endToEndId'=>'','payment.ultimateDebtor.name'=>'', 
                    'payment.ultimateDebtor.partyType'=>'NONE','payment.ultimateDebtor.idType'=>'NONE','payment.ultimateDebtor.identificationNumber'=>'',
                    'payment.ultimateCreditor.name'=>'','payment.ultimateCreditor.partyType'=>'NONE','payment.ultimateCreditor.idType'=>'NONE', 
                    'payment.ultimateCreditor.identificationNumber'=>''
                ), array( 'payment.amount' => $p['amount'] , 'payment.beneficiaryAccountNumber' => trim( $p['dst_acc'] ) )
            );
            unset( $post_data['save'] , $post_data['validate_payment'] );
            if(!$html = $this->post_to_url( self::$url.'/private/d2d/payments2/domestic/new;' . $this->j_session , $post_data ))
            return $this->error( self::ERR_SYS, __LINE__);

            if(!$form = $this->parse_form( $html, 'mainForm' )) return $this->error( self::ERR_READ_FORM , __LINE__);

            $post_data = $this->prepare_post_data( $form['fields'] , array(
                    'field1'=>'validate_payment','value1'=>'true','timestamp'=>time().rand(100,555),'account'=>$account,'definedPaymentId'=>'null',
                    'payment.urgent'=>$urgent,'payment.beneficiaryName'=>$p['benefic'],'payment.beneficiaryBankCode'=>$bic,'detailsChooser'=>'details',
                    'payment.creditor.partyType'=>$reg_type,'payment.creditor.idType'=>$id_type,'payment.creditor.identificationNumber'=>$reg_num,
                    'payment.currency'=>$currency,'payment.details'=>trim($p['details']),'payment.creditorReferenceNumber'=>'','payment.bopCode'=>'',
                    'payment.beneficiaryResidenceCountry'=>'LV','payment.endToEndId'=>'','payment.ultimateDebtor.name'=>'', 
                    'payment.ultimateDebtor.partyType'=>'NONE','payment.ultimateDebtor.idType'=>'NONE','payment.ultimateDebtor.identificationNumber'=>'',
                    'payment.ultimateCreditor.name'=>'','payment.ultimateCreditor.partyType'=>'NONE','payment.ultimateCreditor.idType'=>'NONE', 
                    'payment.ultimateCreditor.identificationNumber'=>''
                ), array( 'payment.amount' => $p['amount'] , 'payment.beneficiaryAccountNumber' => trim( $p['dst_acc'] ) )
            );
            unset( $post_data['save'] , $post_data['validate_payment'] );

            if(!$html = $this->post_to_url( self::$url.'/private/d2d/payments2/domestic/new;' . $this->j_session , $post_data ))
            return $this->error( self::ERR_SYS, __LINE__);

            if( FALSE !== $error = self::parse_error( $html )) return $this->error( $error , __LINE__);

            if( !isset($this->accounts[ $p['dst_acc'] ]))
            {
                if( preg_match( "/<div[^>]*\"challengeResponseBlock\"[^>]*>.*<img[^>]*src=\"(\/password.*)\"/Usiu" , $html, $tmp ) > 0 )
                {
                    file_put_contents( $tmpfname = tempnam( self::get_temp_dir(), 'gif_' ) , $this->read_from_url( self::$url . $tmp[1] ) , FILE_APPEND );
                    if(!$this->captcha = (int)self::read_captcha( $tmpfname  ))return $this->error( self::ERR_OCR , __LINE__ );
                    @unlink( $tmpfname );
                }
                else return $this->error( self::ERR_FIND_CAPTCHA, __LINE__ );
            }

            if(!$form = $this->parse_form( $html, 'mainForm' )) return $this->error( self::ERR_READ_FORM, __LINE__ );

            $post_data = $this->prepare_post_data( $form['fields'],
                array('amountCheck' => $p['amount'],'field1' => 'sign','value1' => 'true','timestamp' => time().rand(555,999)),
                array('challengeDigits' => substr( $this->codes[$this->captcha] , 0 , 3 )));

            if(!$html = $this->post_to_url( self::$url.'/private/d2d/payments2/domestic/new;' . $this->j_session , $post_data ))
                return $this->error( self::ERR_SYS, __LINE__);

            if( preg_match( "/<div[^>]*\"successMessage\"[^>]*>\s*<div[^>]*\"successMsg\"[^>]*>/Usiu" , $html ) > 0 ) return TRUE;

            if( FALSE !== $error = self::parse_error( $html )) return $this->error( $error , __LINE__);

            return $this->error( self::ERR_READ_TSTATUS ,__LINE__);
        }

        public function transfer_funds_eu ( $params ){} /* TO DO -- EUROZONE AREA TRANSFER -- */

        public function transfer_funds_global( $params ){} /* TO DO -- WORLD WIDE TRANSFER -- */

        public function change_password( $new_password , $check_result = TRUE )
        {
            if( !$this->logged_on ) return $this->error( self::ERR_LOGGED_ON, __LINE__ );
            if(!$html = $this->post_to_url( self::$url.'/private/home/important/settings/change_pwd;'.$this->j_session , $this->post_data ))
                return $this->error( self::ERR_SYS, __LINE__);

            if(!$form = $this->parse_form( $html, 'mainForm' )) return $this->error( self::ERR_READ_FORM, __LINE__);

            if(!$html = $this->post_to_url( $form['action'] , $this->prepare_post_data( $form['fields'] , array( 
                'current_password'=>$this->pswrd,'new_password'=>$new_password,'renew_password'=>$new_password,'field1'=>'save','value1'=>'true') ) ))
                    return $this->error( self::ERR_SYS, __LINE__);

            if( FALSE !== $error = self::parse_error( $html )) return $this->error( $error, __LINE__);

            if( !$check_result )
            {
                $this->pswrd = $new_password;
                return TRUE;
            }

            if( preg_match( "/<form[^>]*mainForm[^>]*>.*<div[^>]*class=\"successMsg\"[^>]*>([^>]*)<\/div/Usiu" , $html , $tmp ) > 0 )
                if( strlen( trim( strip_tags( $tmp[1] ) ) ) > 0 )
                {
                    $this->pswrd = $new_password;
                    return TRUE;
                }
            return $this->error( self::ERR_READ_TSTATUS , __LINE__);
        }

        public function get_account_statement( $account, $date_from = '' , $date_to = '' )
        {
            if( !$this->logged_on ) return $this->error( self::ERR_LOGGED_ON, __LINE__ );

            if( '' == trim($date_from) ) $date_from = date('d.m.Y'); if( '' == trim($date_to) ) $date_to = date('d.m.Y');

            if( empty( $this->get_accounts( )) ) return $this->error( self::ERR_READ_ACC ,__LINE__);
            @$account = $this->accounts[$account]['account'] OR $account = '';
            if('' == $account) return $this->error( self::ERR_INVALID_ACC ,__LINE__);

            if(!$html = $this->post_to_url( self::$url.'/private/d2d/accounts/statement;'.$this->j_session , $this->post_data ))
                return $this->error( self::ERR_SYS, __LINE__);

            if(!$form = $this->parse_form( $html, 'mainForm' )) return $this->error( self::ERR_READ_FORM, __LINE__ );

            if(!$csv = $this->post_to_url( $form['action'] , $this->prepare_post_data( $form['fields'] , array(	'account'=>$account , 
                'statementOrder.periodBeg'=>$date_from,'statementOrder.periodEnd'=>$date_to,'statementOrder.media'=>'csv',
                'statementOrder.wideFormat'=>'true','field1'=>'send','value1'=>'true','statementOrder.filterCounterPartyAccount'=>'',
                'statementOrder.filterAmount'=>'','statementOrder.debitCardPayments'=>'','statementOrder.outgoingPayments'=>'', 
                'statementOrder.incomingPayments'=>'','statementOrder.cashPayments'=>'','statementOrder.smsPayments'=>'', 
                'statementOrder.showAccountNumber'=>'' ) ) ) ) return $this->error( self::ERR_SYS, __LINE__);

            $ret_val = array(); $i=0; $lines = explode( "\n" , $csv );
            foreach($lines as $line) if($i++>0&&''!=trim($line))
            {
                $r=str_getcsv($line,',','"');
                if('20'==$r[1])$ret_val[$r[6]]['transactions'][]=array($r[0],$r[1],$r[2],$r[3],$r[4],str_replace(',','.',$r[5]),$r[6],$r[7],$r[8],$r[9],$r[10],$r[11],$r[12]);
                if('10'==$r[1])$ret_val[$r[6]]['begin'] = str_replace(',','.',$r[5]);
                if('86'==$r[1])$ret_val[$r[6]]['end'] = str_replace(',','.',$r[5]);
                if('82'==$r[1])$ret_val[$r[6]]['turnover'] = str_replace(',','.',$r[5]);
            }
            return $ret_val;
        }

        public function logout()
        {
            $ret_val = FALSE;
            if( $this->logged_on ) {
                $post_data = $this->post_data; $post_data['field1']='pageId'; $post_data['value1']='logout';
                $this->post_to_url( self::$url . '/private/d2d;' . $this->j_session . '?forceLogout=true', $post_data );
                $this->logged_on = FALSE;
                $ret_val = TRUE;
            }
            if( $this->persist ) {
                @unlink( $this->cookies );
                @unlink( $this->cookies . '.dat' );
            }
            return $ret_val;
        }


        public function get_accounts( $force_refresh = FALSE )
        {
            $ret_val = array();

            if( !$this->logged_on ) return $this->error( self::ERR_LOGGED_ON, __LINE__ );

            if( !empty( $this->accounts ) && !$force_refresh ) return $this->accounts;

            $html = $this->post_to_url( self::$url . '/private/d2d/accounts/overview;' . $this->j_session , $this->post_data );

            if( preg_match( "/<table[^>]*id=\"tblCurrentAccounts\"[^>]*>.*<tr[^>]*>.*(<tr[^>]*>.*)<\/table/Usiu" , $html, $tmp ) > 0 )
            {
                //print_r( $tmp ); exit();
                if( preg_match_all ( "/<tr[^>]*>(.*)<\/tr/Usiu" , $tmp[1], $trs , PREG_SET_ORDER ) > 0 )
                {
                    $acc = '';
                    foreach( $trs as $tr )
                    {
                        //if( preg_match( "/<td[^>]*>(.*)<\/td>\s*<td[^>]*>(.*)<\/td>\s*<td[^>]*>(.*)<\/td>\s*<td[^>]*>(.*)<\/td>\s*<td[^>]*>(.*)<\/td>\s*<td[^>]*>(.*)<\/td>/Usiu" , $tr[1], $td ) > 0 )
                        //{
                            if( preg_match( "/<a[^>]*'account','(.*)'[^>]*>(.*)<\/a>(.*)</Usiu" , $tr[1], $tmp ) > 0 )
                            {
                                $acc = trim( $tmp[2] );
                                $ret_val[ $acc ]['name'] = trim( str_replace( array("\r","\n","\xA0","\xC2"),'', $tmp[3] ) );
                                $ret_val[ $acc ]['account'] = trim( $tmp[1] );
                            }
                        //    if(''!=$acc)
                        //        $ret_val[$acc]['currency'][trim($td[3])]=array(trim($td[2]),trim($td[4]),trim(strip_tags($td[5])),trim($td[6]));
                        //}
                    }
                    //print_r($ret_val);exit();
                }else return $this->error( self::ERR_PARSE_ACC , __LINE__);
            }else return $this->error( self::ERR_FIND_ACC , __LINE__);
            return $this->accounts = $ret_val;
        }

        static function read_captcha( $f )
        {
            return str_replace( array('l','S','s','G','g','o','O','A'), array('1','5','5','6','9','0','0','4'),
                trim( shell_exec( "giftopnm '$f' | ocrad -s5 --filter=numbers" ) ) );
        }
        private function read_from_url( $url )
        {
            curl_setopt( $this->curl , CURLOPT_URL, $url);
            curl_setopt( $this->curl , CURLOPT_POST, 0 );
            if( !$ret_val = curl_exec( $this->curl ) )return $this->error( self::ERR_HTTP_GET , __LINE__ );
            return $ret_val;
        }
        private function post_to_url($url, $data)
        {
            $fields = '';
            foreach($data as $key => $value) {  $fields .= $key . '=' . urlencode( $value ) . '&'; }
            rtrim($fields, '&');
            curl_setopt( $this->curl, CURLOPT_URL, $url );
            curl_setopt( $this->curl, CURLOPT_POST, count( $data ) );
            curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $fields );
            curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
            $ret_val = curl_exec( $this->curl );
            if( FALSE === $ret_val  )return $this->error( self::ERR_HTTP_POST , __LINE__ );
            return $ret_val;
        }
        private function prepare_post_data( $vars , $vars2 = array() , $vars3 = array() )
        {
            $ret_val = array();
            foreach( $vars as $var )
            {
                if( 'language' == $var[0] ) $ret_val[$var[0]] = 'ENG';
                else if( 'loginType' == $var[0] ) $ret_val[$var[0]] = 3;
                else if( 'userId' == $var[2] ) $ret_val[$var[0]] = $this->login;
                else if( 'authPwd' == $var[2] ) $ret_val[$var[0]] = $this->pswrd;
                else if( 'challengeResponse' == $var[2] ) $ret_val[$var[0]] = $this->codes[$this->captcha];
                else if( '' !== $var[0] ) $ret_val[$var[0]] = $var[1];
                if( '' !== $var[0] && '' !== $var[2] && isset( $vars3[$var[2]] ) ) $ret_val[$var[0]] = $vars3[$var[2]];
            }
            foreach( $vars2 as $key=>$var2 ) $ret_val[$key] = $var2;
            return $ret_val;
        }
        private function parse_form( $html , $id , $atr = 'id' )
        {
            $ret_val = array();
            if( preg_match( "/<form([^>]*$atr=\"$id\"[^>]*)>(.*)<\/form/Usiu" , $html, $tmp ) > 0 )
            {
                $ret_val['id'] = $id;
                if( preg_match( "/method=\"(.*)\"/Usiu", $tmp[1], $t) > 0) $ret_val['method'] = $t[1];
                if( preg_match( "/action=\"(.*)\"/Usiu", $tmp[1], $t) > 0) $ret_val['action'] = self::$url.$t[1];
                if( preg_match( "/name=\"(.*)\"/Usiu", $tmp[1], $t) > 0) $ret_val['name'] = $t[1];
                $ret_val['fields'] = $this->parse_input_fields( $tmp[2] );
            }
            else return $this->error( self::ERR_PARSE_FORM, __LINE__ );
            return $ret_val;
        }
        private function parse_input_fields( $html )
        {
            $ret_val = array();
            if( preg_match_all ( "/<input.*>/Usiu" , $html, $inputs , PREG_SET_ORDER ) > 0 )
            {
                foreach( $inputs as $input )
                {
                    $name = $id = $value = '';
                    if( preg_match( "/value=\"(.*)\"/Usiu" , $input[0], $tmp ) > 0 ) $value = $tmp[1];
                    if( preg_match( "/name=\"(.*)\"/Usiu" , $input[0], $tmp ) > 0 ) $name = $tmp[1];
                    if( preg_match( "/id=\"(.*)\"/Usiu" , $input[0], $tmp ) > 0 ) $id = $tmp[1];
                    $ret_val[] = array( $name , $value , $id );
                }
            }
            return $ret_val;
        }

        static public function get_currency_rates()
        {
            $i=0; $ret_val=array();
            $lines = explode("\n",str_replace(',','.',file_get_contents(self::$url.'/private/d2d/payments/rates/currency/ratesReport?field1=saveToFile&value1=csv')));
            foreach($lines as $line)if($i++>1&&''!=trim($line)){$r=str_getcsv($line,';','"');$ret_val[$r[0]]=array($r[1],$r[2],$r[3],$r[4],$r[5]);}
            return $ret_val;
        }

        private static function parse_error( $html )
        {
            if( preg_match( "/<div[^>]*\"errorMessage\"[^>]*>\s*<div[^>]*\"errorMsg\"[^>]*>([^>]*)(<\/div|<br)/Usiu",$html,$tmp)>0)
                return  strtoupper( preg_replace( '/\s+/', ' ', trim(strip_tags($tmp[1])) ));;
            return FALSE;
        }

        public function get_errors()
        {
            return $this->errors;
        }

        public function get_last_error()
        {
            return array( 'code'=>$this->error_code,'text'=>$this->error_text,'time'=>$this->error_time,'line'=>$this->error_line );
        }

        private function error( $err , $line = 0 )
        {
            $this->error_line = $line;
            @$this->error_time = date("Y-m-d H:i:s");
            if( is_string( $err ) )
            {
                $hash = hash( 'adler32' , $err = trim( $err ) );
                $this->error_code = 300000 + (intval( substr( $hash, 0, 4 ), 16) ^ intval( substr( $hash, 4, 4 ), 16));
                $this->error_text = $err;
            }
            else
            {
                $this->error_text = self::$error_messages[$err];
                $this->error_code = $err;
            }
            $this->errors[] = $this->get_last_error();
            return NULL;
        }

        public function get_temp_dir()
        {
            return rtrim( sys_get_temp_dir(), '/\\' ) . '/';
        }

    }

