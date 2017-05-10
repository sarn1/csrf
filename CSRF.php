<?php
namespace Tyndale;

/*
todo - Phase 2 - Nonce Object - sarnphamornsuwana
@author - sarnphamornsuwana
@date - 8/16/2016
@time - 4:49 PM

Database store the nonces.
*/
class CSRF
{
  private $db = false;                         // phase 2 - database storage
  private $ip = null;
  private $browser = null;
  private $seed = null;                        // seed
  private $expire = 60*60;                      // 1 hour
  private $key = '01192-B-156ASADaxz!!#Q452';  // encryption key - phase 2 make it random, store in db

  public $nonce = null;                       // encrypted seed

  public function __construct()
  {
    $ip_parts = explode( ".", $_SERVER['REMOTE_ADDR'] );
    $this->ip = filter_var( $ip_parts[0],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[1],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[2],FILTER_SANITIZE_NUMBER_INT );
    $this->browser = filter_var( $_SERVER['HTTP_USER_AGENT'],FILTER_SANITIZE_STRING );
  }

  //create a nonce, allows you to set a expiration in minutes
  public function create( $min = null ) {
    if ( $this->db ) {
      //database method - phase 2
    } else {
      //timeout method
      $this->set_expire( $min );
      $this->encrypt( $this->create_uid() );
      return $this;
    }
  }

  //return nonce value in html hidden field
  public function generate_form_field() {
    if (!empty($this->nonce)) {
      return '<input type="hidden" name="nonce" value ="'.$this->nonce.'" />'."\n";
    }

    return null;
  }

  public static function validate( $encrypted_nonce ) {
    $n = new \Tyndale\Nonce();

    $decrypted_seed = json_decode($n->decrypt( $encrypted_nonce ) ,true );

    if (
      time() <= $decrypted_seed['expires'] &&
      $n->ip == $decrypted_seed['ip'] &&
      $n->browser == $decrypted_seed['browser']
    ) {
      return true;
    }

    return false;
  }

  // set expires in minutes
  private function set_expire ( $min ) {
    if ( $min ) {
      $this->expire = $min * 60;
    }
  }

  // create unique identifier
  private function create_uid () {
    $uid = [
      'buffer'  => bin2hex( mcrypt_create_iv(3, MCRYPT_DEV_URANDOM) ),
      'ip'      => $this->ip,
      'buffer2'  => bin2hex( mcrypt_create_iv(3, MCRYPT_DEV_URANDOM) ),
      'browser' => $this->browser,
      'buffer3'  => bin2hex( mcrypt_create_iv(3, MCRYPT_DEV_URANDOM) ),
      'expires' => time() + $this->expire,
    ];
    $this->seed = json_encode( $uid );

    return $this->seed;
  }

  // encrypt engine
  private function encrypt( $seed )
  {
    $this->nonce = trim(
      base64_encode(
        mcrypt_encrypt(
          MCRYPT_RIJNDAEL_256,
          hash( 'sha256', str_pad( $this->key, 32, "\0", STR_PAD_LEFT ), true ), $seed,
          MCRYPT_MODE_ECB,
          mcrypt_create_iv(
            mcrypt_get_iv_size(
              MCRYPT_RIJNDAEL_256,
              MCRYPT_MODE_ECB
            ),
            MCRYPT_RAND
          )
        )
      )
    );
    return $this->nonce;
  }

  private function decrypt( $nonce )
  {
    return trim(
      mcrypt_decrypt(
        MCRYPT_RIJNDAEL_256,
        hash('sha256', str_pad( $this->key, 32, "\0", STR_PAD_LEFT ), true),
        base64_decode($nonce),
        MCRYPT_MODE_ECB,
        mcrypt_create_iv(
          mcrypt_get_iv_size(
            MCRYPT_RIJNDAEL_256,
            MCRYPT_MODE_ECB
          ),
          MCRYPT_RAND
        )
      )
    );
  }


  /*
   * Phase 2 - Database
   * private insert_db - stores the nonce
   * private check_db - validates the nonce, delete if found
   * private clean_db - clean up old nonce
   */

}
