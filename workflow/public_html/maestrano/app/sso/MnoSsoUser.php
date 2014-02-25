<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * ProcessMaker User Management Service
   * @var PDO
   */
  public $rbac = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->rbac = $opts['rbac'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    if ($this->local_id) {
      $this->session['USER_LOGGED']  = $this->local_id;
      $this->session['USR_USERNAME'] = $this->uid;
      $this->session['USR_FULLNAME'] = "$this->name $this->surname";
      $this->session['WORKSPACE'] = SYS_SYS;
      //var_dump($_SESSION);  
      return true;
    } else {
        return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // First create the RBAC user (Security user)
      $rbac_user = $this->buildLocalRbacUser();
      $rbac_role = $this->getRoleIdToAssign();
      $lid = $this->rbac->createUser($rbac_user, $rbac_role);
      
      // Then create the regular user (Worflow user)
      // Create user
      $regular_user = $this->buildLocalRegularUser($lid);
      $user = new Users();
      $user->create( $regular_user );
    }
    
    return $lid;
  }
  
  /**
   * Return a normal user for creation (worflow user)
   *
   * @return a hash of attributes
   */
  protected function buildLocalRegularUser($rbac_user_id)
  {
    $data = $this->buildLocalRbacUser();
    
    $data['USR_STATUS'] = 'ACTIVE';
    $data['USR_UID'] = $rbac_user_id;
    $data['USR_PASSWORD'] = md5( $rbac_user_id ); //fake
    $data['USR_ROLE'] = $this->getRoleIdToAssign();
    
    return $data;
  }
  
  /**
   * Return a rbac user for creation (security user)
   *
   * @return a hash of attributes
   */
  protected function buildLocalRbacUser()
  {
    $data = Array(
      'USR_USERNAME'   => $this->uid,
      'USR_PASSWORD'   => md5($this->generatePassword()),
      'USR_FIRSTNAME'   => $this->name,
      'USR_LASTNAME'    => $this->surname,
      'USR_EMAIL'       => $this->email,
      'USR_DUE_DATE'    => date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) + 2 ) ),
      'USR_CREATE_DATE' => date( 'Y-m-d H:i:s' ),
      'USR_UPDATE_DATE' => date( 'Y-m-d H:i:s' ),
      'USR_BIRTHDAY'    => date( 'Y-m-d' ),
      'USR_STATUS'      => 1,
      'USR_AUTH_TYPE'   => '',
      'UID_AUTH_SOURCE' => '',
      'USR_AUTH_USER_DN' => '',
      
    );
    return $data;
  }
  
  /**
   * Get the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return the ID of the user created, null otherwise
   */
  public function getRoleIdToAssign() 
  {
    $role_id = 'PROCESSMAKER_OPERATOR'; // User
    
    if ($this->app_owner) {
      $role_id = 'PROCESSMAKER_ADMIN'; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role_id = 'PROCESSMAKER_ADMIN';
        } else {
          $role_id = 'PROCESSMAKER_OPERATOR';
        }
      }
    }
    
    return $role_id;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $arg = mysqli_real_escape_string($this->uid);
    
    $conn = Propel::getConnection( RbacUsersPeer::DATABASE_NAME );
    $sql = "SELECT USR_UID FROM USERS WHERE mno_uid = '$arg' LIMIT 1";
    $stmt = $conn->createStatement();
    $rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
    $rs->next();
    $row = $rs->getRow();
    
    if ($result && $result['USR_UID']) {
      return $result['USR_UID'];
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $arg = mysqli_real_escape_string($this->email);
    
    $conn = Propel::getConnection( RbacUsersPeer::DATABASE_NAME );
    $sql = "SELECT USR_UID FROM USERS WHERE USR_EMAIL = '$arg' LIMIT 1";
    $stmt = $conn->createStatement();
    $rs = $stmt->executeQuery($sql, ResultSet::FETCHMODE_ASSOC);
    $rs->next();
    $row = $rs->getRow();
    
    if ($result && $result['USR_UID']) {
      return $result['USR_UID'];
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       $data = Array(
         'username' => addslashes($this->uid),
         'name' => addslashes($this->name),
         'surname' => addslashes($this->surname),
         'email' => addslashes($this->email),
         'id' => addslashes($this->local_id),
       );
       
       $sql = "UPDATE USERS SET
         USR_USERNAME = '" . $data['username'] . "',
         USR_FIRSTNAME = '" . $data['name'] . "',
         USR_LASTNAME = '" . $data['surname'] . "',
         USR_EMAIL = '" . $data['email'] . "'
         WHERE USR_UID = '" . $data['id'] . "'";
       
       $upd = true;
      
      
       foreach( Array(RbacUsersPeer::DATABASE_NAME, UsersPeer::DATABASE_NAME) as $db_name ) {
         $conn = Propel::getConnection( $db_name );
         $upd = $conn->executeUpdate($sql) && $upd;
       }
       
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      
      $data = Array(
        'mno_uid' => addslashes($this->uid),
        'id' => addslashes($this->local_id),
      );
      
      $sql = "UPDATE USERS SET
        mno_uid = '" . $data['mno_uid'] . "'
        WHERE USR_UID = '" . $data['id'] . "'";
      
      $conn = Propel::getConnection( RbacUsersPeer::DATABASE_NAME );
      $upd = $conn->executeUpdate($sql);
      
      return $upd;
    }
    
    return false;
  }
}