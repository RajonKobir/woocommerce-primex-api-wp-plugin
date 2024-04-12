<?php


function primex_encrypt_password($password){

    // if the extension enabled
    if( !extension_loaded('openssl') ){
        return $password;
    }

    // if the functions do not exist
    if ( !function_exists('openssl_cipher_iv_length') || !function_exists('openssl_encrypt') || !function_exists('openssl_decrypt') ) {
        return $password;
    }

    // Storing the cipher method
    $ciphering = "AES-128-CTR";

    // Using OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1234567890112233';

    // Storing the encryption key
    $encryption_key = "mediavita";

    // Using openssl_encrypt() function to encrypt the data
    $encrypted_password = openssl_encrypt($password, $ciphering, $encryption_key, $options, $encryption_iv);

    return $encrypted_password;

}



function primex_decrypt_password($password){

    // if the extension enabled
    if(!extension_loaded('openssl')){
        return $password;
    }

    // if the functions do not exist
    if ( !function_exists('openssl_cipher_iv_length') || !function_exists('openssl_encrypt') || !function_exists('openssl_decrypt') ) {
        return $password;
    }

    // Storing the cipher method
    $ciphering = "AES-128-CTR";

    // Using OpenSSl Decryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for decryption
    $encryption_iv = '1234567890112233';

    // Storing the decryption key
    $encryption_key = "mediavita";

    // Using openssl_decrypt() function to decrypt the data
    $decrypted_password = openssl_decrypt($password, $ciphering, $encryption_key, $options, $encryption_iv);

    return $decrypted_password;

}