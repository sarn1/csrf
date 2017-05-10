# CSRF

This is a PHP Cross-Site Request Forgery (CSRF) prevention library using the Double Cookie Submit method.  This particular method allows easy implementation and scalability versus the Synchronizer Token method which requires a database session.  However, the Synchronizer method may be a future feature added on later.

My goal for this library was to built in something a little more complex than the typical the other Double Cookie Submit libraries out there that only checks the POST value against the cookie. There's a lite version and a full version.  The full version will check the IP and browser headers while the lite version checks just the IP address.  Opt for the light version if you're concern about the length of the nonce created by the library.

## Implementation
Included in the repository is an index.php that has examples on how to implement the CSRF library.

This library has a method that can generate a hidden form input that you can use in your view.

```
$echo (new \Tyndale\CSRF())->generate_form_field();
```
Which would yield this output:

```
<input type="hidden" name="nonce" value ="UlFIVDh2aEN6aGlJK0wwOXR5bVo1eEcwN2grNEZXSGdqR25QT2FEN291Um05dWt5RnFSMGgrczdyTWFhNkdQWA==" />
```
You can then do something like the following snippet to verify the nonce.
```
if ( \Tyndale\CSRF::validate($_POST['nonce']) ) {
  echo 'valid';
};
```
If you just want just the nonce value, that is possible as well. The following code would provide you a nonce value with an expiration of 5 minutes (default is 1 hour if the parameter is left empty).

```
echo (new \Tyndale\CSRF())->create(5);
```

## Usage

Feel free to take it and use/modify as you please.

## For More Information

* [OWASP: Cross-Site Request Forgery (CSRF) Prevention Cheat Sheet](https://www.owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29_Prevention_Cheat_Sheet#Double_Submit_Cookies)
* [Detectify: Login CSRF](https://support.detectify.com/customer/portal/articles/1969819-login-csrf)

[Detectify](https://detectify.com/) is a great tool for scanning your websites for vulnerabilities in your code.
