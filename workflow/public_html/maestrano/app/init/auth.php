<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);

//-----------------------------------------------
// ProcessMaker init code
//-----------------------------------------------
// Defining the PATH_SEP constant, he we are defining if the the path separator symbol will be '\\' or '/'
define( 'PATH_SEP', '/' );

// Force document root path for MAMP
$_SERVER['DOCUMENT_ROOT'] = APP_DIR;

// Defining the Home Directory
$realdocuroot = str_replace( '\\', '/', $_SERVER['DOCUMENT_ROOT'] );
$docuroot = explode( PATH_SEP, $realdocuroot );

array_pop( $docuroot );
$pathhome = implode( PATH_SEP, $docuroot ) . PATH_SEP;

// try to find automatically the trunk directory where are placed the RBAC and Gulliver directories
// in a normal installation you don't need to change it.
array_pop( $docuroot );
$pathTrunk = implode( PATH_SEP, $docuroot ) . PATH_SEP;

array_pop( $docuroot );
$pathOutTrunk = implode( PATH_SEP, $docuroot ) . PATH_SEP;

define( 'PATH_HOME', $pathhome );
define( 'PATH_TRUNK', $pathTrunk );
define( 'PATH_OUTTRUNK', $pathOutTrunk );

//here we are putting approved CONSTANTS, I mean constants be sure we need,
define( 'PATH_HTML', PATH_HOME . 'public_html' . PATH_SEP );

// Defining RBAC Paths constants
define( 'PATH_RBAC_HOME', PATH_TRUNK . 'rbac' . PATH_SEP );

// Defining Gulliver framework paths constants
define( 'PATH_GULLIVER_HOME', PATH_TRUNK . 'gulliver' . PATH_SEP );
define( 'PATH_GULLIVER', PATH_GULLIVER_HOME . 'system' . PATH_SEP ); //gulliver system classes
define( 'PATH_GULLIVER_BIN', PATH_GULLIVER_HOME . 'bin' . PATH_SEP ); //gulliver bin classes
define( 'PATH_TEMPLATE', PATH_GULLIVER_HOME . 'templates' . PATH_SEP );
define( 'PATH_THIRDPARTY', PATH_GULLIVER_HOME . 'thirdparty' . PATH_SEP );
define( 'PATH_RBAC', PATH_RBAC_HOME . 'engine' . PATH_SEP . 'classes' . PATH_SEP ); //to enable rbac version 2
define( 'PATH_RBAC_CORE', PATH_RBAC_HOME . 'engine' . PATH_SEP );

// Defining PMCore Path constants
define( 'PATH_CORE', PATH_HOME . 'engine' . PATH_SEP );
define( 'PATH_SKINS', PATH_CORE . 'skins' . PATH_SEP );
define( 'PATH_SKIN_ENGINE', PATH_CORE . 'skinEngine' . PATH_SEP );
define( 'PATH_METHODS', PATH_CORE . 'methods' . PATH_SEP );
define( 'PATH_XMLFORM', PATH_CORE . 'xmlform' . PATH_SEP );
define( 'PATH_CONFIG', PATH_CORE . 'config' . PATH_SEP );
define( 'PATH_PLUGINS', PATH_CORE . 'plugins' . PATH_SEP );
define( 'PATH_HTMLMAIL', PATH_CORE . 'html_templates' . PATH_SEP );
define( 'PATH_TPL', PATH_CORE . 'templates' . PATH_SEP );
define( 'PATH_TEST', PATH_CORE . 'test' . PATH_SEP );
define( 'PATH_FIXTURES', PATH_TEST . 'fixtures' . PATH_SEP );
define( 'PATH_RTFDOCS', PATH_CORE . 'rtf_templates' . PATH_SEP );
define( 'PATH_DYNACONT', PATH_CORE . 'content' . PATH_SEP . 'dynaform' . PATH_SEP );
//define( 'PATH_LANGUAGECONT',PATH_CORE . 'content' . PATH_SEP . 'languages' . PATH_SEP );
define( 'SYS_UPLOAD_PATH', PATH_HOME . "public_html/files/" );
define( 'PATH_UPLOAD', PATH_HTML . 'files' . PATH_SEP );

define( 'PATH_WORKFLOW_MYSQL_DATA', PATH_CORE . 'data' . PATH_SEP . 'mysql' . PATH_SEP );
define( 'PATH_RBAC_MYSQL_DATA', PATH_RBAC_CORE . 'data' . PATH_SEP . 'mysql' . PATH_SEP );
define( 'FILE_PATHS_INSTALLED', PATH_CORE . 'config' . PATH_SEP . 'paths_installed.php' );
define( 'PATH_WORKFLOW_MSSQL_DATA', PATH_CORE . 'data' . PATH_SEP . 'mssql' . PATH_SEP );
define( 'PATH_RBAC_MSSQL_DATA', PATH_RBAC_CORE . 'data' . PATH_SEP . 'mssql' . PATH_SEP );
define( 'PATH_CONTROLLERS', PATH_CORE . 'controllers' . PATH_SEP );
define( 'PATH_SERVICES_REST', PATH_CORE . 'services' . PATH_SEP . 'rest' . PATH_SEP );

// include Gulliver Class
require_once (PATH_GULLIVER . "class.bootstrap.php");

if (file_exists( FILE_PATHS_INSTALLED )) {

    // include the server installed configuration
    require_once FILE_PATHS_INSTALLED;

    // defining system constant when a valid server environment exists
    define( 'PATH_LANGUAGECONT', PATH_DATA . "META-INF" . PATH_SEP );
    define( 'PATH_CUSTOM_SKINS', PATH_DATA . 'skins' . PATH_SEP );
    define( 'PATH_TEMPORAL', PATH_C . 'dynEditor/' );
    define( 'PATH_DB', PATH_DATA . 'sites' . PATH_SEP );

    // smarty constants
    define( 'PATH_SMARTY_C', PATH_C . 'smarty' . PATH_SEP . 'c' );
    define( 'PATH_SMARTY_CACHE', PATH_C . 'smarty' . PATH_SEP . 'cache' );

    /* TO DO: put these line in other part of code*/
    Bootstrap::verifyPath ( PATH_SMARTY_C,     true );
    Bootstrap::verifyPath ( PATH_SMARTY_CACHE, true );
}

// set include path
set_include_path( PATH_CORE . PATH_SEPARATOR .
                  PATH_THIRDPARTY . PATH_SEPARATOR .
                  PATH_THIRDPARTY . 'pear' . PATH_SEPARATOR .
                  PATH_RBAC_CORE . PATH_SEPARATOR .
                  get_include_path()
);

$config = Bootstrap::getSystemConfiguration();

// Call Gulliver Classes
Bootstrap::LoadThirdParty( 'smarty/libs', 'Smarty.class' );
//loading the autoloader libraries feature
spl_autoload_register(array('Bootstrap', 'autoloadClass'));
Bootstrap::registerClass('G', PATH_GULLIVER . "class.g.php");
Bootstrap::registerClass('System',        PATH_HOME . "engine/classes/class.system.php");

// Call more Classes
Bootstrap::registerClass('headPublisher', PATH_GULLIVER . "class.headPublisher.php");
Bootstrap::registerClass('publisher', PATH_GULLIVER . "class.publisher.php");
Bootstrap::registerClass('xmlform', PATH_GULLIVER . "class.xmlform.php");
Bootstrap::registerClass('XmlForm_Field', PATH_GULLIVER . "class.xmlform.php");
Bootstrap::registerClass('xmlformExtension', PATH_GULLIVER . "class.xmlformExtension.php");
Bootstrap::registerClass('form',         PATH_GULLIVER . "class.form.php");
Bootstrap::registerClass('menu',         PATH_GULLIVER . "class.menu.php");
Bootstrap::registerClass('Xml_Document', PATH_GULLIVER . "class.xmlDocument.php");
Bootstrap::registerClass('DBSession',    PATH_GULLIVER . "class.dbsession.php");
Bootstrap::registerClass('DBConnection', PATH_GULLIVER . "class.dbconnection.php");
Bootstrap::registerClass('DBRecordset',  PATH_GULLIVER . "class.dbrecordset.php");
Bootstrap::registerClass('DBTable',      PATH_GULLIVER . "class.dbtable.php");
Bootstrap::registerClass('xmlMenu',      PATH_GULLIVER . "class.xmlMenu.php");
Bootstrap::registerClass('XmlForm_Field_FastSearch', PATH_GULLIVER . "class.xmlformExtension.php");
Bootstrap::registerClass('XmlForm_Field_XmlMenu', PATH_GULLIVER . "class.xmlMenu.php");
Bootstrap::registerClass('XmlForm_Field_HTML',  PATH_GULLIVER . "class.dvEditor.php");
Bootstrap::registerClass('XmlForm_Field_WYSIWYG_EDITOR',  PATH_GULLIVER . "class.wysiwygEditor.php");
Bootstrap::registerClass('Controller',          PATH_GULLIVER . "class.controller.php");
Bootstrap::registerClass('HttpProxyController', PATH_GULLIVER . "class.httpProxyController.php");
Bootstrap::registerClass('templatePower',            PATH_GULLIVER . "class.templatePower.php");
Bootstrap::registerClass('XmlForm_Field_SimpleText', PATH_GULLIVER . "class.xmlformExtension.php");
Bootstrap::registerClass('Groups',       PATH_HOME . "engine/classes/class.groups.php");
Bootstrap::registerClass('Tasks',        PATH_HOME . "engine/classes/class.tasks.php");
Bootstrap::registerClass('Calendar',     PATH_HOME . "engine/classes/class.calendar.php");
Bootstrap::registerClass('processMap',   PATH_HOME . "engine/classes/class.processMap.php");

Bootstrap::registerSystemClasses();

require_once  PATH_THIRDPARTY . '/pear/PEAR.php';

define('SYS_TEMP','workflow');
require_once (PATH_DB . SYS_TEMP . '/db.php');
define( 'SYS_SYS', SYS_TEMP );

// defining constant for workspace shared directory
define( 'PATH_WORKSPACE', PATH_DB . SYS_SYS . PATH_SEP );
// including workspace shared classes -> particularlly for pmTables
set_include_path( get_include_path() . PATH_SEPARATOR . PATH_WORKSPACE );

Propel::init( PATH_CORE . "config/databases.php" );
Creole::registerDriver ('dbarray', 'creole.contrib.DBArrayConnection');

// Enable RBAC (user management)
Bootstrap::LoadSystem( 'rbac' );
$RBAC = &RBAC::getSingleton( PATH_DATA, session_id() );
$RBAC->sSystem = 'PROCESSMAKER';
$RBAC->initRBAC();


//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
// e.g:
$opts = array();
$opts['rbac'] = $RBAC;

MaestranoService::setAfterSsoSignInPath('/sysworkflow/en/neoclassic/cases/main');


