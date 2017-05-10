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
  private $expire = 60*60;                    // 1 hour
  private $key = 'insanely crazy password';   // encryption key - phase 2 make it random, store in db
  private $cypher = 'AES-128-ECB';
  private $lite_version = true;               //lite version checks only IP, creates a smaller nonce
  private $cookie_name = 'CSRFTOKEN';         //the name of the cookie that stores the nonce

  private $db = false;                        // phase 2 - database storage
  private $ip = null;
  private $browser = null;
  private $data = null;                       // data

  public $nonce = null;                       // for PT easy access else GETTER

  public function __construct()
  {
    $ip_parts = explode( ".", $_SERVER['REMOTE_ADDR'] );
    $this->ip = filter_var( $ip_parts[0],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[1],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[2],FILTER_SANITIZE_NUMBER_INT );
    $this->browser = filter_var( $_SERVER['HTTP_USER_AGENT'],FILTER_SANITIZE_STRING );
  }

  //create a nonce, allows you to set a expiration in minutes
  public function create( $min = null ) {
    if ( $this->db ) {
      //synchronizer tokens (database - session) method - phase 2
    } else {
      //double cookie defense method
      $this->set_expire( $min );
      $this->nonce = $this->encrypt( $this->create_uid() );
      $this->set_cookie();
      return $this;
    }
  }

  //return nonce value in html hidden field
  public function generate_form_field() {
    if ( empty($this->nonce) ) {
      $this->create();
    }

    return '<input type="hidden" name="nonce" value ="'.$this->nonce.'" />'."\n";
  }

  public static function validate( $encrypted_nonce ) {
    $n = new \Tyndale\CSRF();

    $decrypted_data = json_decode( $n->decrypt( $encrypted_nonce ) ,true );

    if ( $n->lite_version ) {
      if ( time() <= $decrypted_data['expires'] && $n->ip == $decrypted_data['ip'] && isset($_COOKIE[$n->cookie_name]) && $_COOKIE[$n->cookie_name] == $encrypted_nonce ) {
        return true;
      }
    } else {
      if ( time() <= $decrypted_data['expires'] && $n->ip == $decrypted_data['ip'] && $n->browser == $decrypted_data['browser'] && isset($_COOKIE[$n->cookie_name]) && $_COOKIE[$n->cookie_name] == $encrypted_nonce ) {
        return true;
      }
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

    if ( $this->lite_version ) {
      $uid = [
        'ip'      => $this->ip,
        'expires' => time() + $this->expire,
      ];
    } else {
      $uid = [
        'ip'      => $this->ip,
        'browser' => $this->browser,
        'expires' => time() + $this->expire,
      ];
    }

    $this->data = json_encode( $uid );

    return $this->data;
  }

  // encrypt engine
  private function encrypt( $data )
  {
    return base64_encode(openssl_encrypt($data, $this->cypher, $this->key));
  }

  private function decrypt( $encrypted_data )
  {
    return openssl_decrypt(base64_decode($encrypted_data), $this->cypher, $this->key);
  }

  private function set_cookie () {
    setcookie( $this->cookie_name , $this->nonce, -1, '/', null, false, true );
  }

  private function get_cookie () {
    return ( isset($_COOKIE[$this->cookie_name]) )? $_COOKIE[$this->cookie_name] : false;
  }


  /*
   * Phase 2 - Database
   * private insert_db - stores the nonce
   * private check_db - validates the nonce, delete if found
   * private clean_db - clean up old nonce
   */

}
