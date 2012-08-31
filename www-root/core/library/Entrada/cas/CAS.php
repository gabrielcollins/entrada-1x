<?php

// commented in 0.4.22-RC2 for Sylvain Derosiaux
// error_reporting(E_ALL ^ E_NOTICE);

//
// hack by Vangelis Haniotakis to handle the absence of $_SERVER['REQUEST_URI'] in IIS
//
if (!$_SERVER['REQUEST_URI']) {
     $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
}

//
// another one by Vangelis Haniotakis also to make phpCAS work with PHP5
//
if (version_compare(PHP_VERSION,'5','>=')) {
    require_once(dirname(__FILE__).'/domxml-php4-php5.php');
}

/**
 * @file CAS/CAS.php
 * Interface class of the phpCAS library
 *
 * @ingroup public
 */

// ########################################################################
//  CONSTANTS
// ########################################################################

// ------------------------------------------------------------------------
//  CAS VERSIONS
// ------------------------------------------------------------------------

/**
 * phpCAS version. accessible for the user by phpCAS::getVersion().
 */
define('PHPCAS_VERSION','0.5.1-1');

// ------------------------------------------------------------------------
//  CAS VERSIONS
// ------------------------------------------------------------------------
/**
 * @addtogroup public
 * @{
 */

/**
 * CAS version 1.0
 */
define("CAS_VERSION_1_0",'1.0');
/*!
 * CAS version 2.0
 */
define("CAS_VERSION_2_0",'2.0');

/** @} */
// ------------------------------------------------------------------------
//  MISC
// ------------------------------------------------------------------------
/**
 * @addtogroup internalMisc
 * @{
 */

/**
 * This global variable is used by the interface class phpCAS.
 *
 * @hideinitializer
 */
$PHPCAS_CLIENT  = null;

/**
 * This global variable is used to store where the initializer is called from 
 * (to print a comprehensive error in case of multiple calls).
 *
 * @hideinitializer
 */
$PHPCAS_INIT_CALL = array('done' => FALSE,
			  'file' => '?',
			  'line' => -1,
			  'method' => '?');

/**
 * This global variable is used to store where the method checking
 * the authentication is called from (to print comprehensive errors)
 *
 * @hideinitializer
 */
$PHPCAS_AUTH_CHECK_CALL = array('done' => FALSE,
				'file' => '?',
				'line' => -1,
				'method' => '?',
				'result' => FALSE);

/**
 * This global variable is used to store phpCAS debug mode.
 *
 * @hideinitializer
 */
$PHPCAS_DEBUG  = array('filename' => FALSE,
		       'indent' => 0,
		       'unique_id' => '');

/** @} */

// ########################################################################
//  CLIENT CLASS
// ########################################################################

// include client class
//include_once(dirname(__FILE__).'/dlient.php');
/**
 * @class CASClient
 * The CASClient class is a client interface that provides CAS authentication
 * to PHP applications.
 *
 * @author Pascal Aubry <pascal.aubry at univ-rennes1.fr>
 */

class CASClient
{

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                          CONFIGURATION                             XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  HTML OUTPUT
  // ########################################################################
  /**
   * @addtogroup internalOutput
   * @{
   */  
	
  /**
   * This method filters a string by replacing special tokens by appropriate values
   * and prints it. The corresponding tokens are taken into account:
   * - __CAS_VERSION__
   * - __PHPCAS_VERSION__
   * - __SERVER_BASE_URL__
   *
   * Used by CASClient::PrintHTMLHeader() and CASClient::printHTMLFooter().
   *
   * @param $str the string to filter and output
   *
   * @private
   */
  function HTMLFilterOutput($str)
    {
      $str = str_replace('__CAS_VERSION__',$this->getServerVersion(),$str);
      $str = str_replace('__PHPCAS_VERSION__',phpCAS::getVersion(),$str);
      $str = str_replace('__SERVER_BASE_URL__',$this->getServerBaseURL(),$str);
      echo $str;
    }

  /**
   * A string used to print the header of HTML pages. Written by CASClient::setHTMLHeader(),
   * read by CASClient::printHTMLHeader().
   *
   * @hideinitializer
   * @private
   * @see CASClient::setHTMLHeader, CASClient::printHTMLHeader()
   */
  var $_output_header = '';
  
  /**
   * This method prints the header of the HTML output (after filtering). If
   * CASClient::setHTMLHeader() was not used, a default header is output.
   *
   * @param $title the title of the page
   *
   * @see HTMLFilterOutput()
   * @private
   */
  function printHTMLHeader($title)
    {
      $this->HTMLFilterOutput(str_replace('__TITLE__',
					  $title,
					  (empty($this->_output_header)
					   ? '<html><head><title>__TITLE__</title></head><body><h1>__TITLE__</h1>'
					   : $this->_output_header)
					  )
			      );
    }

  /**
   * A string used to print the footer of HTML pages. Written by CASClient::setHTMLFooter(),
   * read by printHTMLFooter().
   *
   * @hideinitializer
   * @private
   * @see CASClient::setHTMLFooter, CASClient::printHTMLFooter()
   */
  var $_output_footer = '';
  
  /**
   * This method prints the footer of the HTML output (after filtering). If
   * CASClient::setHTMLFooter() was not used, a default footer is output.
   *
   * @see HTMLFilterOutput()
   * @private
   */
  function printHTMLFooter()
    {
      $this->HTMLFilterOutput(empty($this->_output_footer)
			      ?('<hr><address>phpCAS __PHPCAS_VERSION__ using server <a href="__SERVER_BASE_URL__">__SERVER_BASE_URL__</a> (CAS __CAS_VERSION__)</a></address></body></html>')
			      :$this->_output_footer);
    }

  /**
   * This method set the HTML header used for all outputs.
   *
   * @param $header the HTML header.
   *
   * @public
   */
  function setHTMLHeader($header)
    {
      $this->_output_header = $header;
    }

  /**
   * This method set the HTML footer used for all outputs.
   *
   * @param $footer the HTML footer.
   *
   * @public
   */
  function setHTMLFooter($footer)
    {
      $this->_output_footer = $footer;
    }

  /** @} */
  // ########################################################################
  //  CAS SERVER CONFIG
  // ########################################################################
  /**
   * @addtogroup internalConfig
   * @{
   */  
  
  /**
   * a record to store information about the CAS server.
   * - $_server["version"]: the version of the CAS server
   * - $_server["hostname"]: the hostname of the CAS server
   * - $_server["port"]: the port the CAS server is running on
   * - $_server["uri"]: the base URI the CAS server is responding on
   * - $_server["base_url"]: the base URL of the CAS server
   * - $_server["login_url"]: the login URL of the CAS server
   * - $_server["service_validate_url"]: the service validating URL of the CAS server
   * - $_server["proxy_url"]: the proxy URL of the CAS server
   * - $_server["proxy_validate_url"]: the proxy validating URL of the CAS server
   * - $_server["logout_url"]: the logout URL of the CAS server
   *
   * $_server["version"], $_server["hostname"], $_server["port"] and $_server["uri"]
   * are written by CASClient::CASClient(), read by CASClient::getServerVersion(), 
   * CASClient::getServerHostname(), CASClient::getServerPort() and CASClient::getServerURI().
   *
   * The other fields are written and read by CASClient::getServerBaseURL(), 
   * CASClient::getServerLoginURL(), CASClient::getServerServiceValidateURL(), 
   * CASClient::getServerProxyValidateURL() and CASClient::getServerLogoutURL().
   *
   * @hideinitializer
   * @private
   */
  var $_server = array(
		       'version' => -1,
		       'hostname' => 'none',
		       'port' => -1,
		       'uri' => 'none'
		       );
  
  /**
   * This method is used to retrieve the version of the CAS server.
   * @return the version of the CAS server.
   * @private
   */
  function getServerVersion()
    { 
      return $this->_server['version']; 
    }

  /**
   * This method is used to retrieve the hostname of the CAS server.
   * @return the hostname of the CAS server.
   * @private
   */
  function getServerHostname()
    { return $this->_server['hostname']; }

  /**
   * This method is used to retrieve the port of the CAS server.
   * @return the port of the CAS server.
   * @private
   */
  function getServerPort()
    { return $this->_server['port']; }

  /**
   * This method is used to retrieve the URI of the CAS server.
   * @return a URI.
   * @private
   */
  function getServerURI()
    { return $this->_server['uri']; }

  /**
   * This method is used to retrieve the base URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerBaseURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['base_url']) ) {
	$this->_server['base_url'] = 'https://'
	  .$this->getServerHostname()
	  .':'
	  .$this->getServerPort()
	  .$this->getServerURI();
      }
      return $this->_server['base_url']; 
    }

  /**
   * This method is used to retrieve the login URL of the CAS server.
   * @param $gateway true to check authentication, false to force it
   * @return a URL.
   * @private
   */
  function getServerLoginURL($gateway=false)
    { 
      phpCAS::traceBegin();
      // the URL is build only when needed
      if ( empty($this->_server['login_url']) ) {
        $this->_server['login_url'] = $this->getServerBaseURL();
        $this->_server['login_url'] .= 'login?service=';
//        $this->_server['login_url'] .= preg_replace('/&/','%26',$this->getURL());
        $this->_server['login_url'] .= urlencode($this->getURL());
        if ($gateway) {
          $this->_server['login_url'] .= '&gateway=true';
        }
      }
      phpCAS::traceEnd($this->_server['login_url']);
      return $this->_server['login_url']; 
    }

  /**
   * This method sets the login URL of the CAS server.
   * @param $url the login URL
   * @private
   * @since 0.4.21 by Wyman Chan
   */
  function setServerLoginURL($url)
    {
      return $this->_server['login_url'] = $url;
    }

  /**
   * This method is used to retrieve the service validating URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerServiceValidateURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['service_validate_url']) ) {
	switch ($this->getServerVersion()) {
	case CAS_VERSION_1_0:
	  $this->_server['service_validate_url'] = $this->getServerBaseURL().'validate';
	  break;
	case CAS_VERSION_2_0:
	  $this->_server['service_validate_url'] = $this->getServerBaseURL().'ucserviceValidate';
	  break;
	}
      }
//      return $this->_server['service_validate_url'].'?service='.preg_replace('/&/','%26',$this->getURL()); 
      return $this->_server['service_validate_url'].'?service='.urlencode($this->getURL()); 
    }

  /**
   * This method is used to retrieve the proxy validating URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerProxyValidateURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['proxy_validate_url']) ) {
	switch ($this->getServerVersion()) {
	case CAS_VERSION_1_0:
	  $this->_server['proxy_validate_url'] = '';
	  break;
	case CAS_VERSION_2_0:
	  $this->_server['proxy_validate_url'] = $this->getServerBaseURL().'ucserviceValidate';
	  break;
	}
      }
//      return $this->_server['proxy_validate_url'].'?service='.preg_replace('/&/','%26',$this->getURL()); 
      return $this->_server['proxy_validate_url'].'?service='.urlencode($this->getURL()); 
    }

   /**
   * This method is used to retrieve the logout URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerLogoutURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['logout_url']) ) {
	$this->_server['logout_url'] = $this->getServerBaseURL().'logout';
      }
      return $this->_server['logout_url']; 
    }

  /**
   * This method sets the logout URL of the CAS server.
   * @param $url the logout URL
   * @private
   * @since 0.4.21 by Wyman Chan
   */
  function setServerLogoutURL($url)
    {
      return $this->_server['logout_url'] = $url;
    }

  /**
   * This method checks to see if the request is secured via HTTPS
   * @return true if https, false otherwise
   * @private
   */
  function isHttps() {
    //if ( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ) {
    //0.4.24 by Hinnack
    if ( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
      return true;
    } else {
      return false;
    }
  }

  // ########################################################################
  //  CONSTRUCTOR
  // ########################################################################
   /**
    * CASClient constructor.
    *
    * @param $server_version the version of the CAS server
    * @param $proxy TRUE if the CAS client is a CAS proxy, FALSE otherwise
    * @param $server_hostname the hostname of the CAS server
    * @param $server_port the port the CAS server is running on
    * @param $server_uri the URI the CAS server is responding on
    * @param $start_session Have phpCAS start PHP sessions (default true)
    *
    * @return a newly created CASClient object
    *
    * @public
    */
  function CASClient(
  	$server_version,
	$proxy,
	$server_hostname,
	$server_port,
	$server_uri,
	$start_session = true) {

    phpCAS::traceBegin();
    
    //activate session mechanism if desired
    if ($start_session) {
      session_start();
    }

    $this->_proxy = $proxy;

    //check version
/*    switch ($server_version) {
      case CAS_VERSION_1_0:
/*        if ( $this->isProxy() )
          phpCAS::error('CAS proxies are not supported in CAS '
              .$server_version);
        break;
*      case CAS_VERSION_2_0:
        break;
      default:
        phpCAS::error('this version of CAS (`'
            .$server_version
            .'\') is not supported by phpCAS '
            .phpCAS::getVersion());
    }
 */   $this->_server['version'] = $server_version;

    //check hostname
    if ( empty($server_hostname) 
        || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/',$server_hostname) ) {
      phpCAS::error('bad CAS server hostname (`'.$server_hostname.'\')');
    }
    $this->_server['hostname'] = $server_hostname;

    //check port
    if ($server_port == 0 || ! (int) $server_port ) {
      phpCAS::error('bad CAS server port (`'.$server_port.'\')');
    }
    $this->_server['port'] = $server_port;

    //check URI
    if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/',$server_uri) ) {
      phpCAS::error('bad CAS server URI (`'.$server_uri.'\')');
    }
    //add leading and trailing `/' and remove doubles      
    $server_uri = preg_replace('/\/\//','/','/'.$server_uri.'/');
    $this->_server['uri'] = $server_uri;

    //set to callback mode if PgtIou and PgtId CGI GET parameters are provided 
/*    if ( $this->isProxy() ) {
      $this->setCallbackMode(!empty($_GET['pgtIou'])&&!empty($_GET['pgtId']));
    }
*/
/*    if ( $this->isCallbackMode() ) {
      //callback mode: check that phpCAS is secured
      if ( !$this->isHttps() ) {
        phpCAS::error('CAS proxies must be secured to use phpCAS; PGT\'s will not be received from the CAS server');
      }
    } else {
 */     //normal mode: get ticket and remove it from CGI parameters for developpers
      $ticket = (isset($_GET['ticket']) ? $_GET['ticket'] : null);
/*      switch ($this->getServerVersion()) {
        case CAS_VERSION_1_0: // check for a Service Ticket
          if( preg_match('/^ST-/',$ticket) ) {
            phpCAS::trace('ST \''.$ticket.'\' found');
            //ST present
            $this->setST($ticket);
            //ticket has been taken into account, unset it to hide it to applications
            unset($_GET['ticket']);
          } else if ( !empty($ticket) ) {
            //ill-formed ticket, halt
            phpCAS::error('ill-formed ticket found in the URL (ticket=`'.htmlentities($ticket).'\')');
          }
          break;
        case CAS_VERSION_2_0: // check for a Service or Proxy Ticket
*/          if( preg_match('/^[SP]T-/',$ticket) ) {
            phpCAS::trace('ST or PT \''.$ticket.'\' found');
            $this->setPT($ticket);
            unset($_GET['ticket']);
          } else if ( !empty($ticket) ) {
            //ill-formed ticket, halt
            phpCAS::error('ill-formed ticket found in the URL (ticket=`'.htmlentities($ticket).'\')');
          } 
//d          break;
//d        }
//d	}
    phpCAS::traceEnd();
  }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                           AUTHENTICATION                           XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  /**
   * @addtogroup internalAuthentication
   * @{
   */  
  
  /**
   * The Authenticated user. Written by CASClient::setUser(), read by CASClient::getUser().
   * @attention client applications should use phpCAS::getUser().
   *
   * @hideinitializer
   * @private
   */
  var $_user = '';
  var $_ucid = '';
  
  /**
   * This method sets the CAS user's login name.
   *
   * @param $user the login name of the authenticated user.
   *
   * @private
   */
  function setUser($user)
    {
      $this->_user = $user;
    }


  function setUcid($ucid)
    {
      $this->_ucid = $ucid;
    }



  /**
   * This method returns the CAS user's login name.
   * @warning should be called only after CASClient::forceAuthentication() or 
   * CASClient::isAuthenticated(), otherwise halt with an error.
   *
   * @return the login name of the authenticated user
   */
  function getUser()
    {
      if ( empty($this->_user) ) {
	phpCAS::error('this method should be used only after '.__CLASS__.'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()');
      }
      return $this->_user;
    }

  function getUcid()
    {
      if ( empty($this->_ucid) ) {
	phpCAS::error('this method should be used only after '.__CLASS__.'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()');
      }
      return $this->_ucid;
    }

  /**
   * This method is called to be sure that the user is authenticated. When not 
   * authenticated, halt by redirecting to the CAS server; otherwise return TRUE.
   * @return TRUE when the user is authenticated; otherwise halt.
   * @public
   */
  function forceAuthentication()
    {
      phpCAS::traceBegin();

      if ( $this->isAuthenticated() ) {
        // the user is authenticated, nothing to be done.
	    phpCAS::trace('no need to authenticate');
	    $res = TRUE;
      } else {
	    // the user is not authenticated, redirect to the CAS server
        unset($_SESSION['phpCAS']['auth_checked']);
        $this->redirectToCas(FALSE/* no gateway */);	
	    // never reached
	    $res = FALSE;
      }
      phpCAS::traceEnd($res);
      return $res;
    }

  /**
   * An integer that gives the number of times authentication will be cached before rechecked.
   *
   * @hideinitializer
   * @private
   */
  var $_cache_times_for_auth_recheck = 0;
  
  /**
   * Set the number of times authentication will be cached before rechecked.
   *
   * @param $n an integer.
   *
   * @public
   */
  function setCacheTimesForAuthRequest($n)
    {
      $this->_cache_times_for_auth_recheck = n;
    }

  /**
   * This method is called to check whether the user is authenticated or not.
   * @return TRUE when the user is authenticated, FALSE otherwise.
   * @public
   */
  function checkAuthentication()
    {
      phpCAS::traceBegin();

      if ( $this->isAuthenticated() ) {
	    phpCAS::trace('user is authenticated');
	    $res = TRUE;
      } else if (isset($_SESSION['phpCAS']['auth_checked'])) {
        // the previous request has redirected the client to the CAS server with gateway=true
        unset($_SESSION['phpCAS']['auth_checked']);
        $res = FALSE;
      } else {
//        $_SESSION['phpCAS']['auth_checked'] = true;
//	    $this->redirectToCas(TRUE/* gateway */);	
//	    // never reached
//	    $res = FALSE;
        // avoid a check against CAS on every request
        if (! isset($_SESSION['phpCAS']['unauth_count']) )
           $_SESSION['phpCAS']['unauth_count'] = -2; // uninitialized
        
        if (($_SESSION['phpCAS']['unauth_count'] != -2 && $this->_cache_times_for_auth_recheck == -1) 
          || ($_SESSION['phpCAS']['unauth_count'] >= 0 && $_SESSION['phpCAS']['unauth_count'] < $this->_cache_times_for_auth_recheck))
        {
           $res = FALSE;
           
           if ($this->_cache_times_for_auth_recheck != -1)
           {
		   	  $_SESSION['phpCAS']['unauth_count']++;
           	  phpCAS::trace('user is not authenticated (cached for '.$_SESSION['phpCAS']['unauth_count'].' times of '.$this->_cache_times_for_auth_recheck.')');
           }
           else
           {
           	  phpCAS::trace('user is not authenticated (cached for until login pressed)');
           }
        }
        else
        {
         	$_SESSION['phpCAS']['unauth_count'] = 0;
            $_SESSION['phpCAS']['auth_checked'] = true;
            phpCAS::trace('user is not authenticated (cache reset)');
    	    $this->redirectToCas(TRUE/* gateway */);	
    	    // never reached
    	    $res = FALSE;
        }
      }
      phpCAS::traceEnd($res);
      return $res;
    }
  
  /**
   * This method is called to check if the user is authenticated (previously or by
   * tickets given in the URL).
   *
   * @return TRUE when the user is authenticated.
   *
   * @public
   */
  function isAuthenticated()
  {
      phpCAS::traceBegin();
      $res = FALSE;
      $validate_url = '';

      if ( $this->wasPreviouslyAuthenticated() ) {
	  	 // the user has already (previously during the session) been 
		 // authenticated, nothing to be done.
    	phpCAS::trace('user was already authenticated, no need to look for tickets');
    	$res = TRUE;
      } 
/*	  elseif ( $this->hasST() ) {
    	// if a Service Ticket was given, validate it
    	phpCAS::trace('ST `'.$this->getST().'\' is present');
    	$this->validateST($validate_url,$text_response,$tree_response); // if it fails, it halts
    	phpCAS::trace('ST `'.$this->getST().'\' was validated');
/*    	if ( $this->isProxy() ) {
		   $this->validatePGT($validate_url,$text_response,$tree_response); // idem
		   phpCAS::trace('PGT `'.$this->getPGT().'\' was validated');
		   $_SESSION['phpCAS']['pgt'] = $this->getPGT();
		}
*d		$_SESSION['phpCAS']['user'] = $this->getUser();
		$_SESSION['phpCAS']['ucid'] = $this->getUcid();
		$res = TRUE;
	}
*/	elseif ( $this->hasPT() ) {
		// if a Proxy Ticket was given, validate it
		phpCAS::trace('PT `'.$this->getPT().'\' is present');
		$this->validatePT($validate_url,$text_response,$tree_response); // note: if it fails, it halts
		phpCAS::trace('PT `'.$this->getPT().'\' was validated');
/*		if ( $this->isProxy() ) {
		   $this->validatePGT($validate_url,$text_response,$tree_response); // idem
		   phpCAS::trace('PGT `'.$this->getPGT().'\' was validated');
		   $_SESSION['phpCAS']['pgt'] = $this->getPGT();
		}
 */   	$_SESSION['phpCAS']['user'] = $this->getUser();
    	$_SESSION['phpCAS']['ucid'] = $this->getUcid();
		$res = TRUE;
	} 
	else {
    	// no ticket given, not authenticated
    	phpCAS::trace('no ticket found');
	}

	phpCAS::traceEnd($res);
	return $res;
  }
  
  /**
   * This method tells if the current session is authenticated.
   * @return true if authenticated based soley on $_SESSION variable
   * @since 0.4.22 by Brendan Arnold
   */
  function isSessionAuthenticated ()
    {
      return !empty($_SESSION['phpCAS']['user']);
    }

  /**
   * This method tells if the user has already been (previously) authenticated
   * by looking into the session variables.
   *
   * @note This function switches to callback mode when needed.
   *
   * @return TRUE when the user has already been authenticated; FALSE otherwise.
   *
   * @private
   */
  function wasPreviouslyAuthenticated()
    {
      phpCAS::traceBegin();

/*      if ( $this->isCallbackMode() ) {
	$this->callback();
      }

      $auth = FALSE;
*/
	// `simple' CAS client (not a proxy): username must be present
	if ( $this->isSessionAuthenticated() ) {
	  // authentication already done
	  $this->setUser($_SESSION['phpCAS']['user']);
	  $this->setUcid($_SESSION['phpCAS']['ucid']);
	  phpCAS::trace('user = `'.$_SESSION['phpCAS']['user'].'\''); 
	  $auth = TRUE;
	} else {
	  phpCAS::trace('no user found');
	}
     
      
      phpCAS::traceEnd($auth);
      return $auth;
    }
  
  /**
   * This method is used to redirect the client to the CAS server.
   * It is used by CASClient::forceAuthentication() and CASClient::checkAuthentication().
   * @param $gateway true to check authentication, false to force it
   * @public
   */
  function redirectToCas($gateway=false)
    {
      phpCAS::traceBegin();
      $cas_url = $this->getServerLoginURL($gateway);
      header('Location: '.$cas_url);
      $this->printHTMLHeader('CAS Authentication wanted!');
      printf('<p>You should already have been redirected to the CAS server. Click <a href="%s">here</a> to continue.</p>',$cas_url);
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }
  
  /**
   * This method is used to logout from CAS.
   * @param $url a URL that will be transmitted to the CAS server (to come back to when logged out)
   * @public
   */
  function logout($url = "")
    {
      phpCAS::traceBegin();
      $cas_url = $this->getServerLogoutURL();
      if ( $url != "" ) {
        $url = '?service=' . $url;
      }
      header('Location: '.$cas_url . $url);
      session_unset();
      session_destroy();
      $this->printHTMLHeader('CAS logout wanted!');
      printf('<p>You should already have been redirected to the CAS server. Click <a href="%s">here</a> to continue.</p>',$cas_url);
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }
  
  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                  BASIC CLIENT FEATURES (CAS 1.0)                   XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  /**
   * @addtogroup internalBasic
   * @{
   */  
  
  /** @} */

  function readURL($url,$cookies,&$headers,&$body,&$err_msg)
    {
      phpCAS::traceBegin();
      $headers = '';
      $body = '';
      $err_msg = '';

      $res = TRUE;

      // initialize the CURL session
      $ch = curl_init($url);
	
	  // verify the the server's certificate corresponds to its name
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	  // but do not verify the certificate itself
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

      // return the CURL output into a variable
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // include the HTTP header with the body
      curl_setopt($ch, CURLOPT_HEADER, 1);
      // add cookies headers
      if ( is_array($cookies) ) {
	curl_setopt($ch,CURLOPT_COOKIE,implode(';',$cookies));
      }
      // perform the query
      $buf = curl_exec ($ch);
      if ( $buf === FALSE ) {
	phpCAS::trace('cur_exec() failed');
	$err_msg = 'CURL error #'.curl_errno($ch).': '.curl_error($ch);
	// close the CURL session
	curl_close ($ch);
	$res = FALSE;
      } else {
	// close the CURL session
	curl_close ($ch);
	
	// find the end of the headers
	// note: strpos($str,"\n\r\n\r") does not work (?)
	$pos = FALSE;
	for ($i=0; $i<strlen($buf); $i++) {
	  if ( $buf[$i] == chr(13) ) 
	    if ( $buf[$i+1] == chr(10) ) 
	      if ( $buf[$i+2] == chr(13) ) 
		if ( $buf[$i+3] == chr(10) ) {
		  // header found
		  $pos = $i;
		  break;
		}
	}
	
	if ( $pos === FALSE ) {
	  // end of header not found
	  $err_msg = 'no header found';
	  phpCAS::trace($err_msg);
	  $res = FALSE;
	} else { 
	  // extract headers into an array
	  $headers = preg_split ("/[\n\r]+/",substr($buf,0,$pos));	  
	  // extract body into a string
	  $body = substr($buf,$pos+4);
	}
      }

      phpCAS::traceEnd($res);
      return $res;
    }

   /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                  PROXIED CLIENT FEATURES (CAS 2.0)                 XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  PT
  // ########################################################################
  /**
   * @addtogroup internalProxied
   * @{
   */  
  
  /**
   * the Proxy Ticket provided in the URL of the request if present
   * (empty otherwise). Written by CASClient::CASClient(), read by 
   * CASClient::getPT() and CASClient::hasPGT().
   *
   * @hideinitializer
   * @private
   */
  var $_pt = '';
  
  /**
   * This method returns the Proxy Ticket provided in the URL of the request.
   * @return The proxy ticket.
   * @private
   */
  function getPT()
    {
      return 'ST'.substr($this->_pt, 2);
    }

  /**
   * This method stores the Proxy Ticket.
   * @param $pt The Proxy Ticket.
   * @private
   */
  function setPT($pt)
    { $this->_pt = $pt; }

  /**
   * This method tells if a Proxy Ticket was stored.
   * @return TRUE if a Proxy Ticket has been stored.
   * @private
   */
  function hasPT()
    { return !empty($this->_pt); }


  /** @} */
  // ########################################################################
  //  PT VALIDATION
  // ########################################################################
  /**
   * @addtogroup internalProxied
   * @{
   */  

  /**
   * This method is used to validate a PT; halt on failure
   * 
   * @return bool TRUE when successfull, halt otherwise by calling CASClient::authError().
   *
   * @private
   */
  function validatePT(&$validate_url,&$text_response,&$tree_response)
    {
      phpCAS::traceBegin();
      // build the URL to validate the ticket
      $validate_url = $this->getServerProxyValidateURL().'&ticket='.$this->getPT();

      // open and read the URL
      if ( !$this->readURL($validate_url,''/*cookies*/,$headers,$text_response,$err_msg) ) {
	phpCAS::trace('could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')');
	$this->authError('PT not validated',
			 $validate_url,
			 TRUE/*$no_response*/);
      }

      // read the response of the CAS server into a DOM object
      if ( !($dom = domxml_open_mem($text_response))) {
	// read failed
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      // read the root node of the XML tree
      if ( !($tree_response = $dom->document_element()) ) {
	// read failed
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      // insure that tag name is 'serviceResponse'
      if ( $tree_response->node_name() != 'serviceResponse' ) {
	// bad root node
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      if ( sizeof($arr = $tree_response->get_elements_by_tagname("authenticationSuccess")) != 0) {
	// authentication succeded, extract the user name
	if ( sizeof($arr = $tree_response->get_elements_by_tagname("user")) == 0) {
	  // no user specified => error
	  $this->authError('PT not validated',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	$this->setUser(trim($arr[0]->get_content()));

	if ( sizeof($arr = $tree_response->get_elements_by_tagname("ucid")) != 0) {
	  $ucidList=trim($arr[0]->get_content()) ;
	  if (count($arr)>1)
	    {
	      	  $ucidList= $ucidList . trim($arr[1]->get_content()) ;
	    }
	  $this->setUcid( $ucidList);
	}
	else
	  {
	      $this->setUcid( "000000");
	  }


	
      } else if ( sizeof($arr = $tree_response->get_elements_by_tagname("authenticationFailure")) != 0) {
	// authentication succeded, extract the error code and message
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     FALSE/*$bad_response*/,
		     $text_response,
		     $arr[0]->get_attribute('code')/*$err_code*/,
		     trim($arr[0]->get_content())/*$err_msg*/);
      } else {
	$this->authError('PT not validated',
		     $validate_url,	
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      
      // at this step, PT has been validated and $this->_user has been set,

      phpCAS::traceEnd(TRUE);
      return TRUE;
    }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                               MISC                                 XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  /**
   * @addtogroup internalMisc
   * @{
   */  
  
  // ########################################################################
  //  URL
  // ########################################################################
  /**
   * the URL of the current request (without any ticket CGI parameter). Written 
   * and read by CASClient::getURL().
   *
   * @hideinitializer
   * @private
   */
  var $_url = '';

  /**
   * This method returns the URL of the current request (without any ticket
   * CGI parameter).
   *
   * @return The URL
   *
   * @private
   */
  function getURL()
    {
      phpCAS::traceBegin();
      // the URL is built when needed only
      if ( empty($this->_url) ) {
	    $final_uri = '';
	    // remove the ticket if present in the URL
	    $final_uri = ($this->isHttps()) ? 'https' : 'http';
	    $final_uri .= '://';
	    /* replaced by Julien Marchal - v0.4.6
	     * $this->_url .= $_SERVER['SERVER_NAME'];
	     */
        if(empty($_SERVER['HTTP_X_FORWARDED_SERVER'])){
          /* replaced by teedog - v0.4.12
           * $this->_url .= $_SERVER['SERVER_NAME'];
           */
          if (empty($_SERVER['SERVER_NAME'])) {
            $server_name = $_SERVER['HTTP_HOST'];
          } else {
            $server_name = $_SERVER['SERVER_NAME'];
          }
        } else {
          $server_name = $_SERVER['HTTP_X_FORWARDED_SERVER'];
        }
      $final_uri .= $server_name;
      if (!strpos($server_name, ':')) {
  	    if ( ($this->isHttps() && $_SERVER['SERVER_PORT']!=443)
	       || (!$this->isHttps() && $_SERVER['SERVER_PORT']!=80) ) {
	      $final_uri .= ':';
	      $final_uri .= $_SERVER['SERVER_PORT'];
	    }
      }

	  $final_uri .= strtok($_SERVER['REQUEST_URI'],"?");
	  $cgi_params = '?'.strtok("?");
	  // remove the ticket if present in the CGI parameters
	  $cgi_params = preg_replace('/&ticket=[^&]*/','',$cgi_params);
	  $cgi_params = preg_replace('/\?ticket=[^&;]*/','?',$cgi_params);
	  $cgi_params = preg_replace('/\?%26/','?',$cgi_params);
	  $cgi_params = preg_replace('/\?&/','?',$cgi_params);
	  $cgi_params = preg_replace('/\?$/','',$cgi_params);
	  $final_uri .= $cgi_params;
	  $this->setURL($final_uri);
    }
    phpCAS::traceEnd($this->_url);
    return $this->_url;
  }

  /**
   * This method sets the URL of the current request 
   *
   * @param $url url to set for service
   *
   * @private
   */
  function setURL($url)
    {
      $this->_url = $url;
    }
  
  // ########################################################################
  //  AUTHENTICATION ERROR HANDLING
  // ########################################################################
  /**
   * This method is used to print the HTML output when the user was not authenticated.
   *
   * @param $failure the failure that occured
   * @param $cas_url the URL the CAS server was asked for
   * @param $no_response the response from the CAS server (other 
   * parameters are ignored if TRUE)
   * @param $bad_response bad response from the CAS server ($err_code
   * and $err_msg ignored if TRUE)
   * @param $cas_response the response of the CAS server
   * @param $err_code the error code given by the CAS server
   * @param $err_msg the error message given by the CAS server
   *
   * @private
   */
  function authError($failure,$cas_url,$no_response,$bad_response='',$cas_response='',$err_code='',$err_msg='')
    {
      phpCAS::traceBegin();

//      $this->printHTMLHeader('CAS Authentication failed!');
      printf('<p>You were not authenticated.</p><p>You may submit your request again by clicking <a href="%s">here</a>.</p><p>If the problem persists, you may contact <a href="mailto:%s">the administrator of this site</a>.</p>',$this->getURL(),$_SERVER['SERVER_ADMIN']);
      phpCAS::trace('CAS URL: '.$cas_url);
      phpCAS::trace('Authentication failure: '.$failure);
      if ( $no_response ) {
	phpCAS::trace('Reason: no response from the CAS server');
      } else {
	if ( $bad_response ) {
	    phpCAS::trace('Reason: bad response from the CAS server');
	} else {
	    if ( empty($err_code) )
	      phpCAS::trace('Reason: no CAS error');
	    else
	      phpCAS::trace('Reason: ['.$err_code.'] CAS error: '.$err_msg);
	}
	phpCAS::trace('CAS response: '.$cas_response);
      }
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }

  /** @} */
}

// ########################################################################
//  INTERFACE CLASS
// ########################################################################

/**
 * @class phpCAS
 * The phpCAS class is a simple container for the phpCAS library. It provides CAS
 * authentication for web applications written in PHP.
 *
 * @ingroup public
 * @author Pascal Aubry <pascal.aubry at univ-rennes1.fr>
 *
 * \internal All its methods access the same object ($PHPCAS_CLIENT, declared 
 * at the end of CAS/client.php).
 */



class phpCAS
{

  // ########################################################################
  //  INITIALIZATION
  // ########################################################################

  /**
   * @addtogroup publicInit
   * @{
   */

  /**
   * phpCAS client initializer.
   * @note Only one of the phpCAS::client() and phpCAS::proxy functions should be
   * called, only once, and before all other methods (except phpCAS::getVersion()
   * and phpCAS::setDebug()).
   *
   * @param $server_version the version of the CAS server
   * @param $server_hostname the hostname of the CAS server
   * @param $server_port the port the CAS server is running on
   * @param $server_uri the URI the CAS server is responding on
   * @param $start_session Have phpCAS start PHP sessions (default true)
   *
   * @return a newly created CASClient object
   */
  function client($server_version,
		  $server_hostname,
		  $server_port,
		  $server_uri,
 		  $start_session = true)
    {
      global $PHPCAS_CLIENT, $PHPCAS_INIT_CALL;

/*      phpCAS::traceBegin();
      if ( is_object($PHPCAS_CLIENT) ) {
	phpCAS::error($PHPCAS_INIT_CALL['method'].'() has already been called (at '.$PHPCAS_INIT_CALL['file'].':'.$PHPCAS_INIT_CALL['line'].')');
      }
      if ( gettype($server_version) != 'string' ) {
	phpCAS::error('type mismatched for parameter $server_version (should be `string\')');
      }
      if ( gettype($server_hostname) != 'string' ) {
	phpCAS::error('type mismatched for parameter $server_hostname (should be `string\')');
      }
      if ( gettype($server_port) != 'integer' ) {
	phpCAS::error('type mismatched for parameter $server_port (should be `integer\')');
      }
      if ( gettype($server_uri) != 'string' ) {
	phpCAS::error('type mismatched for parameter $server_uri (should be `string\')');
      }

      // store where the initialzer is called from
      $dbg = phpCAS::backtrace();
      $PHPCAS_INIT_CALL = array('done' => TRUE,
				'file' => $dbg[0]['file'],
				'line' => $dbg[0]['line'],
				'method' => __CLASS__.'::'.__FUNCTION__);

      // initialize the global object $PHPCAS_CLIENT
*/      $PHPCAS_CLIENT = new CASClient($server_version,FALSE,$server_hostname,$server_port,$server_uri,$start_session);
//      phpCAS::traceEnd();
    }

  /** @} */
  // ########################################################################
  //  DEBUGGING
  // ########################################################################

  /**
   * @addtogroup publicDebug
   * @{
   */

  /**
   * Set/unset debug mode
   *
   * @param $filename the name of the file used for logging, or FALSE to stop debugging.
   */
  function setDebug($filename='')
    {
      global $PHPCAS_DEBUG;

      if ( $filename != FALSE && gettype($filename) != 'string' ) {
	phpCAS::error('type mismatched for parameter $dbg (should be FALSE or the name of the log file)');
      }

      if ( empty($filename) ) {
      	if ( preg_match('/^Win.*/',getenv('OS')) ) {
      	  if ( isset($_ENV['TMP']) ) {
      	    $debugDir = $_ENV['TMP'].'/';
      	  } else if ( isset($_ENV['TEMP']) ) {
      	    $debugDir = $_ENV['TEMP'].'/';
      	  } else {
      	    $debugDir = '';
      	  }
      	} else {
      	  $debugDir = '/tmp/';
      	}
      	$filename = $debugDir . 'phpCAS.log';
      }

      if ( empty($PHPCAS_DEBUG['unique_id']) ) {
	$PHPCAS_DEBUG['unique_id'] = substr(strtoupper(md5(uniqid(''))),0,4);
      }

      $PHPCAS_DEBUG['filename'] = $filename;

      phpCAS::trace('START ******************');
    }
  
  /** @} */
  /**
   * @addtogroup internalDebug
   * @{
   */

  /**
   * This method is a wrapper for debug_backtrace() that is not available 
   * in all PHP versions (>= 4.3.0 only)
   */
  function backtrace()
    {
      if ( function_exists('debug_backtrace') ) {
        return debug_backtrace();
      } else {
        // poor man's hack ... but it does work ...
        return array();
      }
    }

  /**
   * Logs a string in debug mode.
   *
   * @param $str the string to write
   *
   * @private
   */
  function log($str)
    {
      $indent_str = ".";
      global $PHPCAS_DEBUG;

      if ( $PHPCAS_DEBUG['filename'] ) {
	for ($i=0;$i<$PHPCAS_DEBUG['indent'];$i++) {
	  $indent_str .= '|    ';
	}
	error_log($PHPCAS_DEBUG['unique_id'].' '.$indent_str.$str."\n",3,$PHPCAS_DEBUG['filename']);
      }

    }
  
  /**
   * This method is used by interface methods to print an error and where the function
   * was originally called from.
   *
   * @param $msg the message to print
   *
   * @private
   */
  function error($msg)
    {
      $dbg = phpCAS::backtrace();
      $function = '?';
      $file = '?';
      $line = '?';
      if ( is_array($dbg) ) {
	for ( $i=1; $i<sizeof($dbg); $i++) {
	  if ( is_array($dbg[$i]) ) {
	    if ( $dbg[$i]['class'] == __CLASS__ ) {
	      $function = $dbg[$i]['function'];
	      $file = $dbg[$i]['file'];
	      $line = $dbg[$i]['line'];
	    }
	  }
	}
      }
      echo "<br />\n<b>phpCAS error</b>: <font color=\"FF0000\"><b>".__CLASS__."::".$function.'(): '.htmlentities($msg)."</b></font> in <b>".$file."</b> on line <b>".$line."</b><br />\n";
      phpCAS::trace($msg);
      phpCAS::traceExit();
      exit();
    }

  /**
   * This method is used to log something in debug mode.
   */
  function trace($str)
    {
      $dbg = phpCAS::backtrace();
      phpCAS::log($str.' ['.basename($dbg[1]['file']).':'.$dbg[1]['line'].']');
    }

  /**
   * This method is used to indicate the start of the execution of a function in debug mode.
   */
  function traceBegin()
    {
      global $PHPCAS_DEBUG;

      $dbg = phpCAS::backtrace();
      $str = '=> ';
      if ( !empty($dbg[2]['class']) ) {
	$str .= $dbg[2]['class'].'::';
      }
      $str .= $dbg[2]['function'].'(';      
      if ( is_array($dbg[2]['args']) ) {
	foreach ($dbg[2]['args'] as $index => $arg) {
	  if ( $index != 0 ) {
	    $str .= ', ';
	  }
	  $str .= str_replace("\n","",var_export($arg,TRUE));
	}
      }
      $str .= ') ['.basename($dbg[2]['file']).':'.$dbg[2]['line'].']';
      phpCAS::log($str);
      $PHPCAS_DEBUG['indent'] ++;
    }

  /**
   * This method is used to indicate the end of the execution of a function in debug mode.
   *
   * @param $res the result of the function
   */
  function traceEnd($res='')
    {
      global $PHPCAS_DEBUG;

      $PHPCAS_DEBUG['indent'] --;
      $dbg = phpCAS::backtrace();
      $str = '';
      $str .= '<= '.str_replace("\n","",var_export($res,TRUE));
      phpCAS::log($str);
    }

  /**
   * This method is used to indicate the end of the execution of the program
   */
  function traceExit()
    {
      global $PHPCAS_DEBUG;

      phpCAS::log('exit()');
      while ( $PHPCAS_DEBUG['indent'] > 0 ) {
	phpCAS::log('-');
	$PHPCAS_DEBUG['indent'] --;
      }
    }

   /** @} */
  // ########################################################################
  //  VERSION
  // ########################################################################
  /**
   * @addtogroup public
   * @{
   */

  /**
   * This method returns the phpCAS version.
   *
   * @return the phpCAS version.
   */
  function getVersion()
    {
      return PHPCAS_VERSION;
    }
  
  /** @} */
  // ########################################################################
  //  HTML OUTPUT
  // ########################################################################
  /**
   * @addtogroup publicOutput
   * @{
   */

  /**
   * This method sets the HTML header used for all outputs.
   *
   * @param $header the HTML header.
   */
  function setHTMLHeader($header)
    {
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      if ( gettype($header) != 'string' ) {
	phpCAS::error('type mismatched for parameter $header (should be `string\')');
      }
      $PHPCAS_CLIENT->setHTMLHeader($header);
    }

  /**
   * This method sets the HTML footer used for all outputs.
   *
   * @param $footer the HTML footer.
   */
  function setHTMLFooter($footer)
    {
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      if ( gettype($footer) != 'string' ) {
	phpCAS::error('type mismatched for parameter $footer (should be `string\')');
      }
      $PHPCAS_CLIENT->setHTMLFooter($footer);
    }

  /** @} */
   /** @} */
  // ########################################################################
  //  AUTHENTICATION
  // ########################################################################
  /**
   * @addtogroup publicAuth
   * @{
   */

  /**
   * Set the times authentication will be cached before really accessing the CAS server in gateway mode: 
   * - -1: check only once, and then never again (until you pree login)
   * - 0: always check
   * - n: check every "n" time
   *
   * @param $n an integer.
   */
  function setCacheTimesForAuthRecheck($n)
    {
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      if ( gettype($header) != 'integer' ) {
        phpCAS::error('type mismatched for parameter $header (should be `string\')');
      }
      $PHPCAS_CLIENT->setCacheTimesForAuthRecheck($n);
    }
  
  /**
   * This method is called to check if the user is authenticated (use the gateway feature).
   * @return TRUE when the user is authenticated; otherwise FALSE.
   */
  function checkAuthentication()
    {
      global $PHPCAS_CLIENT, $PHPCAS_AUTH_CHECK_CALL;

      phpCAS::traceBegin();
      if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }

      $auth = $PHPCAS_CLIENT->checkAuthentication();

      // store where the authentication has been checked and the result
      $dbg = phpCAS::backtrace();
      $PHPCAS_AUTH_CHECK_CALL = array('done' => TRUE,
				      'file' => $dbg[0]['file'],
				      'line' => $dbg[0]['line'],
				      'method' => __CLASS__.'::'.__FUNCTION__,
				      'result' => $auth );
      phpCAS::traceEnd($auth);
      return $auth; 
    }
  
  /**
   * This method is called to force authentication if the user was not already 
   * authenticated. If the user is not authenticated, halt by redirecting to 
   * the CAS server.
   */
  function forceAuthentication()
    {
      global $PHPCAS_CLIENT, $PHPCAS_AUTH_CHECK_CALL;

      phpCAS::traceBegin();
      if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      
      $auth = $PHPCAS_CLIENT->forceAuthentication();
    phpCAS::trace("No Need to authenticate Force 1 ; auth: $auth");
      
      // store where the authentication has been checked and the result
      $dbg = phpCAS::backtrace();
      $PHPCAS_AUTH_CHECK_CALL = array('done' => TRUE,
				      'file' => $dbg[0]['file'],
				      'line' => $dbg[0]['line'],
				      'method' => __CLASS__.'::'.__FUNCTION__,
				      'result' => $auth );

      if ( !$auth ) {
        phpCAS::trace('user is not authenticated, DLH redirecting to the CAS server');
        $PHPCAS_CLIENT->forceAuthentication();
      } else {
        phpCAS::trace('no need to authenticate (user `'.phpCAS::getUser().'\' is already authenticated)');
      }

      phpCAS::traceEnd();
      return $auth; 
    }
  
  /**
   * This method is called to check if the user is authenticated (previously or by
   * tickets given in the URL).
   *
   * @return TRUE when the user is authenticated.
   */
  function isAuthenticated()
    {
      global $PHPCAS_CLIENT, $PHPCAS_AUTH_CHECK_CALL;

      phpCAS::traceBegin();
      if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }

      // call the isAuthenticated method of the global $PHPCAS_CLIENT object
      $auth = $PHPCAS_CLIENT->isAuthenticated();

      // store where the authentication has been checked and the result
      $dbg = phpCAS::backtrace();
      $PHPCAS_AUTH_CHECK_CALL = array('done' => TRUE,
                                     'file' => $dbg[0]['file'],
                                     'line' => $dbg[0]['line'],
                                     'method' => __CLASS__.'::'.__FUNCTION__,
                                     'result' => $auth );
      phpCAS::traceEnd($auth);
      return $auth;
    }
  
  /**
   * Checks whether authenticated based on $_SESSION. Useful to avoid
   * server calls.
   * @return true if authenticated, false otherwise.
   * @since 0.4.22 by Brendan Arnold
   */
  function isSessionAuthenticated ()
	{
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      return($PHPCAS_CLIENT->isSessionAuthenticated());
    }

  /**
   * This method returns the CAS user's login name.
   * @warning should not be called only after phpCAS::forceAuthentication()
   * or phpCAS::checkAuthentication().
   *
   * @return the login name of the authenticated user
   */
  function getUser()
    {
      global $PHPCAS_CLIENT, $PHPCAS_AUTH_CHECK_CALL;
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      if ( !$PHPCAS_AUTH_CHECK_CALL['done'] ) {
	phpCAS::error('this method should only be called after '.__CLASS__.'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()');
      }
      if ( !$PHPCAS_AUTH_CHECK_CALL['result'] ) {
	phpCAS::error('authentication was checked (by '.$PHPCAS_AUTH_CHECK_CALL['method'].'() at '.$PHPCAS_AUTH_CHECK_CALL['file'].':'.$PHPCAS_AUTH_CHECK_CALL['line'].') but the method returned FALSE');
      }
      return $PHPCAS_CLIENT->getUser();
    }

  function getUcid()
    {
      global $PHPCAS_CLIENT;
      return $PHPCAS_CLIENT->getUcid();
    }

  /**
   * This method returns the URL to be used to login.
   * or phpCAS::isAuthenticated().
   *
   * @return the login name of the authenticated user
   */
  function getServerLoginURL()
    {
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      return $PHPCAS_CLIENT->getServerLoginURL();
    }

  /**
   * Set the login URL of the CAS server.
   * @param $url the login URL
   * @since 0.4.21 by Wyman Chan
   */
  function setServerLoginURL($url='')
   {
     global $PHPCAS_CLIENT;
     phpCAS::traceBegin();
     if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should only be called after
'.__CLASS__.'::client()');
     }
     if ( gettype($url) != 'string' ) {
        phpCAS::error('type mismatched for parameter $url (should be
`string\')');
     }
     $PHPCAS_CLIENT->setServerLoginURL($url);
     phpCAS::traceEnd();
   }

  /**
   * This method returns the URL to be used to login.
   * or phpCAS::isAuthenticated().
   *
   * @return the login name of the authenticated user
   */
  function getServerLogoutURL()
    {
      global $PHPCAS_CLIENT;
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should not be called before '.__CLASS__.'::client() or '.__CLASS__.'::proxy()');
      }
      return $PHPCAS_CLIENT->getServerLogoutURL();
    }

  /**
   * Set the logout URL of the CAS server.
   * @param $url the logout URL
   * @since 0.4.21 by Wyman Chan
   */
  function setServerLogoutURL($url='')
   {
     global $PHPCAS_CLIENT;
     phpCAS::traceBegin();
     if ( !is_object($PHPCAS_CLIENT) ) {
        phpCAS::error('this method should only be called after
'.__CLASS__.'::client()');
     }
     if ( gettype($url) != 'string' ) {
        phpCAS::error('type mismatched for parameter $url (should be
`string\')');
     }
     $PHPCAS_CLIENT->setServerLogoutURL($url);
     phpCAS::traceEnd();
   }

  /**
   * This method is used to logout from CAS. Halts by redirecting to the CAS server.
   * @param $url a URL that will be transmitted to the CAS server (to come back to when logged out)
   */
  function logout($url = "")
    {
      global $PHPCAS_CLIENT;

      phpCAS::traceBegin();
      if ( !is_object($PHPCAS_CLIENT) ) {
	phpCAS::error('this method should only be called after '.__CLASS__.'::client() or'.__CLASS__.'::proxy()');
      }
      $PHPCAS_CLIENT->logout($url);
      // never reached
      phpCAS::traceEnd();
    }
}
?>
