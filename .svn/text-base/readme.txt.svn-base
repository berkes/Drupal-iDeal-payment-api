iDeal payment API

PREREQUISITES

- Drupal 5.X


INSTALLATION

Install and activate this module like every other Drupal
module.
This module needs openssl-extension for PHP enabled.

!!IMPORTANT!!
RABO THINMPI SUPPORT IS NOT YET IMPLEMENTED!!
-Download the "thinmpi" or "iDEALConnector" PHP librairy from the iDEAL controlpanel/dashboard, available to you when you have an iDEAL account. This can be inside a "PHP programming example" archive in the documents section of the iDEAL dashboard.
Due to licencing restriction this code can not be included on Drupal.org.
-ThinMPI: NOT YET SUPPORTED!!
  -Copy the contents of the entire extracted thinmpi directory to the module directory "ideal_payment_api/lib". The directorystructure should be like "ideal_payment_api/lib/iDEALConnector.php"
  -Open "LoadConf.php" file for editing and change the function name "LoadConfiguration()" to something else like "donotLoadConfiguration()". Failing to do so will hang your site upon module installation.
  -Copy private key + cert to "lib/security/" directory.
-iDEALConnector:
  -Copy the contents of the entire extracted connector directory to the module directory "ideal_payment_api/lib".  The directorystructure should be like "ideal_payment_api/lib/ThinMPI.php"
  -Open "iDEALConnector.php" file for editing and add this line  "    return LoadConfiguration(); //Qrios hack for iDEAL" just below "	function loadConfig()" on line 722 on this moment. Make sure to add this after the opening bracket "{". If you don't do this the "includes/security/config.conf" file will be used for settings, the iDEAL settings will be discarded.
  -Open "iDEALConnector_config.inc.php" file for editing and replace the line  "define( "SECURE_PATH"...." on line 10 on this moment for "define( "SECURE_PATH", $_SERVER['DOCUMENT_ROOT'].'/'.drupal_get_path('module', 'ideal_payment_api')."/lib/includes/security");". If you don't do this you can configure this setting manually. This will give you the opportunity to locate this directory outside the web root, though the contents are protected by a .htaccess file.
  -Copy private key + cert to "lib/includes/security/" directory.
-Thats it, see docs + FAQ in iDEAL dashboard for more info.


API

ideal_payment_api_payment_page($order_id, $order_desc, $order_total, $path_back_error, $path_back_succes)

Arguments:
  $order_id, integer, must be unique, or else payment will be rejected
  $order_desc, string
  $order_total, decimal number ###,##
  $path_back_error, string, drupal path to redirect to on unsuccesfull payment. Probably the originating product/order form.
  $path_back_succes, string, drupal path to redirect to on succesfull payment. Probably the home page or a "thank you" page.


DESCRIPTION

Receive payments through checkout via Ideal ING/Postbank Advanced or Rabo Professional.


TOUBLESHOOTING

-Blank page when trying to submit an order: Review Drupal log for "Cannot modify header information - headers already sent by..." messages, remove empty lines after closing PHP tag "?>" in the file mentioned in the log message.


AUTHOR

C. Kodde
Qrios Webdiensten
http://qrios.nl
c.kodde NOatSPAM qrios dot nl


SPONSOR

webschuur
