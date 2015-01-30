<?php
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or utf8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;

$config['db']['default']['hostname'] = 'rdsba2mvy77vzme.mysql.rds.aliyuncs.com';
$config['db']['default']['username'] = 'lipian';
$config['db']['default']['password'] = 'h8OAwp33Pn';
$config['db']['default']['database'] = 'account';
$config['db']['default']['dbdriver'] = 'mysql';
$config['db']['default']['dbprefix'] = '';
$config['db']['default']['pconnect'] = TRUE;
$config['db']['default']['db_debug'] = TRUE;
$config['db']['default']['cache_on'] = FALSE;
$config['db']['default']['cachedir'] = '';
$config['db']['default']['char_set'] = 'utf8';
$config['db']['default']['dbcollat'] = 'utf8_general_ci';
$config['db']['default']['swap_pre'] = '';
$config['db']['default']['autoinit'] = TRUE;
$config['db']['default']['stricton'] = FALSE;

$config['db']['lipian']['hostname'] = 'rdsba2mvy77vzme.mysql.rds.aliyuncs.com';
$config['db']['lipian']['username'] = 'lipian';
$config['db']['lipian']['password'] = 'h8OAwp33Pn';
$config['db']['lipian']['database'] = 'lipian';
$config['db']['lipian']['dbdriver'] = 'mysql';
$config['db']['lipian']['dbprefix'] = 'ccvms_';
$config['db']['lipian']['pconnect'] = TRUE;
$config['db']['lipian']['db_debug'] = TRUE;
$config['db']['lipian']['cache_on'] = FALSE;
$config['db']['lipian']['cachedir'] = '';
$config['db']['lipian']['char_set'] = 'utf8';
$config['db']['lipian']['dbcollat'] = 'utf8_general_ci';
$config['db']['lipian']['swap_pre'] = '';
$config['db']['lipian']['autoinit'] = TRUE;
$config['db']['lipian']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */