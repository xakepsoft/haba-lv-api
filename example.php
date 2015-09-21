<?php

    require 'HabaLV.php';
    use XakepSoft\Banking\HabaLV as Haba;


    $rates = Haba::get_currency_rates(); // This is a static method so we don't need to create an object
    print_r($rates);



    // SwedBank code-card example
    // Older code-cards have 24 numbers, the new ones have more than 70.
    // These codes are not real and provided only as an example.
    $codes = array (
        1=>'435435' ,  9=>'324234' , 17=>'637990' ,
        2=>'345345' , 10=>'435434' , 18=>'234442' ,
        3=>'435353' , 11=>'234234' , 19=>'234442' ,
        4=>'345345' , 12=>'324234' , 20=>'707015' ,
        5=>'234234' , 13=>'811705' , 21=>'324244' ,
        6=>'234234' , 14=>'324234' , 22=>'066135' ,
        7=>'234234' , 15=>'324244' , 23=>'324234' ,
        8=>'234234' , 16=>'324234' , 24=>'234234' );

    $haba = new Haba( $codes );    // Object creation & initialization



    if( !$haba->login( '582233', 'my-password', TRUE ))  // Logging in. The third parameter deals with password change requests from bank. Set it to TRUE and forget about it!
    {
        echo "Unable to login!<br />\n";
        print_r( $haba->get_errors() );
        exit();
    }

    echo "Logged in successfully !<br />\n";


    // Person to Person transfer (Simple mode)
    $status = $haba->transfer_funds_local( array (
            'src_acc' => 'LV91HABA0523423423445' ,   // <-- Your SwedBank account
            'dst_acc' => 'LV66HABA0551001439854' ,   // <-- Destination account
            'benefic' => 'John Smith' ,
            'details' => 'Just testing !' ,
            'amount' => 0.01 ));
    if(!$status) print_r( $haba->get_last_error() ); // Check for error



    // Person to Person transfer (Normal mode)
    $status = $haba->transfer_funds_local( array (
            'src_acc' => 'LV91HABA0554395843435' ,   // <-- Your SwedBank account
            'dst_acc' => 'LV66HABA0551001439854' ,   // <-- Destination account
            'benefic' => 'John Smith' ,
            'details' => 'Just testing !' ,
            'amount' => 0.01 ,
            'currency' => 'USD' ,                    // <-- Set transaction currency. (EUR is by default)
            'reg_type' => 'PRIVATE' ,                // <-- Set to PRIVATE if you know the Personal Identification Number
            'reg_num' => '100382-10329' ));          // <-- Receiver's Personal Identification Number
    if(!$status) print_r( $haba->get_last_error() ); // Check for error

 

    // Person to Business transfer
    $status = $haba->transfer_funds_local( array (
            'src_acc' => 'LV91HABA0551454437544' ,   // <-- Your SwedBank account
            'dst_acc' => 'LV66HABA0551001439854' ,   // <-- Destination account
            'benefic' => 'SIA Po rogam & kapitam' ,
            'details' => 'Just testing !' ,
            'amount' => 0.01 ,
            'reg_type' => 'ORGANISATION' ,           // <-- Set to ORGANISATION if receiver is a company
            'reg_num' => 'LV4000925343' ));          // <-- Receiver's  Tax Identification Number
    if(!$status) print_r( $haba->get_last_error() ); // Check for error




    // Get information about all transactions for a period of time
    $statement = $haba->get_account_statement( 'LV91HABA0557405678270', '20.06.2015' , '25.09.2015' );
    if(!$statement) print_r( $haba->get_last_error() ); // Check for error
    print_r( $statement );



    $status = $haba->change_password('my-new-password'); // Change password
    if(!$status) print_r( $haba->get_last_error() );     // Check for error


    print_r( $haba->get_accounts() ); // Get basic information about accounts


    print_r( $haba->get_errors() );  // Get all errors


    $haba->logout();

