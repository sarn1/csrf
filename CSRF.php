<?php
namespace Tyndale;

/**
 * Class CSRF
 *
 * @package Tyndale
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

  
  /**
   * CSRF constructor.
   */
  public function __construct()
  {
    $ip_parts = explode( ".", $_SERVER['REMOTE_ADDR'] );
    $this->ip = filter_var( $ip_parts[0],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[1],FILTER_SANITIZE_NUMBER_INT ) . "." . filter_var( $ip_parts[2],FILTER_SANITIZE_NUMBER_INT );
    $this->browser = filter_var( $_SERVER['HTTP_USER_AGENT'],FILTER_SANITIZE_STRING );
  }


  /**
   * Creates the nonce value and returns it
   *
   * @param null $min|int     The number of minutes before the nonce expires.
   *
   * @return null|string      Returns the generated nonce.
   */
  public function create( $min = null ) {
    if ( $this->db ) {
      //synchronizer tokens (database - session) method - phase 2
    } else {
      //double cookie defense method
      $this->set_expire( $min );
      $this->nonce = $this->encrypt( $this->create_uid() );
      $this->set_cookie();
      return $this->nonce;
    }
  }


  /**
   * Generates the nonce HTML string.
   *
   * @return string   HTML hidden input tag containing nonce.
   */
  public function generate_form_field() {
    if ( empty($this->nonce) ) {
      $this->create();
    }

    return '<input type="hidden" name="nonce" value ="'.$this->nonce.'" />'."\n";
  }


  /**
   * Validates the nonce.
   *
   * @param $encrypted_nonce
   *
   * @return bool
   */
  public static function validate( $encrypted_nonce ) {
    $n = new \Tyndale\CSRF();

    $decrypted_data = json_decode( $n->decrypt( $encrypted_nonce ) ,true );

    if ( $n->lite_version ) {
      if ( time() <= $decrypted_data['expires'] && $n->ip == $decrypted_data['ip'] && $n->get_cookie() == $encrypted_nonce ) {
        return true;
      }
    } else {
      if ( time() <= $decrypted_data['expires'] && $n->ip == $decrypted_data['ip'] && $n->browser == $decrypted_data['browser'] &&  $n->get_cookie() == $encrypted_nonce ) {
        return true;
      }
    }
    return false;
  }


  /**
   * Sets the nonce expiration.
   *
   * @param $min|int  Set the expiration of the nonce in minutes.
   */
  private function set_expire ( $min ) {
    if ( $min ) {
      $this->expire = $min * 60;
    }
  }


  /**
   * Creates the unique identifier makeup of the user.
   *
   * @return null|string Returns the unique identifier makeup of the visitor
   */
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


  /**
   * Encrypt function.
   *
   * @param $data|string    The data to be encrypted.
   *
   * @return string         The encrypted string.
   */
  private function encrypt( $data )
  {
    return base64_encode(openssl_encrypt($data, $this->cypher, $this->key));
  }


  /**
   * Decrypt function.
   *
   * @param $encrypted_data|string   The encrypted string.
   *
   * @return string                 The unencrypyred string
   */
  private function decrypt( $encrypted_data )
  {
    return openssl_decrypt(base64_decode($encrypted_data), $this->cypher, $this->key);
  }


  /**
   * Sets the nonce value to the cookie.
   */
  private function set_cookie () {
    setcookie( $this->cookie_name , $this->nonce, -1, '/', null, false, true );
  }


  /**
   * Get the nonce value from the cookie.
   *
   * @return bool|string  Gets the value in the cookie if it exists, else returns false
   */
  private function get_cookie () {
    return ( isset($_COOKIE[$this->cookie_name]) )? $_COOKIE[$this->cookie_name] : false;
  }

}
