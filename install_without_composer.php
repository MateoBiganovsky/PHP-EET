<?php
/**
 * This file is part of the PHP-EET package.
 *
 * (c) Filip Sedivy <mail@filipsedivy.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 * @author Filip Sedivy <mail@filipsedivy.cz>
 */

/**
 * @var bool Zapnutí nebo vypnutí ladícího prostředí
*/
define('DEBUG', true);

/**
 * Výpis textu do konzole nebo do prohlížeče
 * @param string $text Vstupní text
*/
function write($text)
{
    if(!DEBUG) { return; }
    if (php_sapi_name() == 'cli') {
        print(addslashes($text).PHP_EOL);
        return;
    }

    echo addslashes($text)."<br>";
}

// Kontrola minimálních požadavků
if(!file_exists(__DIR__.'/composer.json'))
{
    write('File composer.json is not exists');
    copy( 'https://raw.githubusercontent.com/filipsedivy/PHP-EET/master/composer.json', __DIR__.'/composer.json');
}
$composerJson = json_decode(file_get_contents(__DIR__.'/composer.json'), true);
$minimalPhpVersion = $composerJson['require']['php'];

preg_match('/([\W]+)([0-9.]+)/', $minimalPhpVersion, $phpMatch);
list($input, $operator, $version) = $phpMatch;

if(!version_compare(PHP_VERSION, $version, $operator))
{
    write('Your version of PHP is not compatible with this library');
    write('The minimum version is: ');
    write('Current version of PHP: '.PHP_VERSION);
    exit();
}

foreach($composerJson['require'] as $bundle => $version)
{
    if(substr($bundle, 0, 3) == 'ext')
    {
        $bundleExt = substr($bundle, 4);
        if(!in_array($bundleExt, get_loaded_extensions()))
        {
            write($bundleExt.' is not available on your web server');
            exit(0);
        }
    }
}

/** @var array Potřebné třídy */
$necessaryClasses = array('ZipArchive');

/** @var array Závislosti */
$dependency = array(
  "PHP-EET"     => "http://github.com/filipsedivy/PHP-EET/zipball/master/",
  "WSE-PHP"     => "http://github.com/robrichards/wse-php/zipball/master/",
  "XMLSecLibs"  => "https://github.com/robrichards/xmlseclibs/zipball/master"
);


// Kontrola existence tříd
foreach($necessaryClasses as $class)
{
    if(!class_exists($class))
    {
        throw new Exception('This bundle needs the '.$class.' PHP extension.');
    }
}

// Stažení závislostí
foreach($dependency as $name => $url)
{
    $dependencyPath = __DIR__.'/'.$name.'.zip';
    write('Check dependence '.$name);
    if(file_exists($dependencyPath))
    {
        unlink($dependencyPath);
        write('Existing dependency '.$name.' was removed');
    }

    copy($url, $dependencyPath);
    write('Dependency '.$name.' has been downloaded');


    // V případě neexistence složky EETLib se vytvoří
    if(!file_exists(__DIR__.'/EETLib') || !is_dir(__DIR__.'/EETLib'))
    {
        mkdir(__DIR__.'/EETLib', 0777);
        write('The EETLib folder was created');
    }


    // Do této složky se rozzipují soubory
    $ZipObject = new ZipArchive;
    if($ZipObject->open($dependencyPath) === TRUE){
        $ZipObject->extractTo(__DIR__.'/EETLib');
        $ZipObject->close();
        write('Dependency '.$name.' has been unpacked');
    }else{
        write('Dependency '.$name.' can not be opened');
    }

    unlink($dependencyPath);
    write('The '.$name.' file has been removed');
}

// Vytvoření autoloaderu
ob_start(); ?>
function EETLib_Autoloader($class)
{
  // Mapování složek
  $path = array(
    "PHP-EET"     => basename(glob(__DIR__."/filipsedivy-PHP-EET*")[0]),
    "WSE-PHP"     => basename(glob(__DIR__."/robrichards-wse-php*")[0]),
    "XMLSecLibs"  => basename(glob(__DIR__."/robrichards-xmlseclibs*")[0])
  );

  // Mapování objektů
  $map = array(
    "FilipSedivy\\EET\\Certificate"     => $path["PHP-EET"] . "/src/Certificate.php",
    "FilipSedivy\\EET\\Dispatcher"      => $path["PHP-EET"] . "/src/Dispatcher.php",
    "FilipSedivy\\EET\\Receipt"         =>  $path["PHP-EET"] . "/src/Receipt.php",
    "FilipSedivy\\EET\\SoapClient"      => $path["PHP-EET"] . "/src/SoapClient.php",

    "FilipSedivy\\EET\\Utils\\UUID"     => $path["PHP-EET"] . "/src/Utils/UUID.php",
    "FilipSedivy\\EET\\Utils\\Format"   => $path["PHP-EET"] . "/src/Utils/Format.php",

    "FilipSedivy\\EET\\Exceptions\\CertificateException"  => $path["PHP-EET"] . "/src/Exceptions/CertificateException.php",
    "FilipSedivy\\EET\\Exceptions\\ClientException"       => $path["PHP-EET"] . "/src/Exceptions/ClientException.php",
    "FilipSedivy\\EET\\Exceptions\\EetException"          => $path["PHP-EET"] . "/src/Exceptions/EetException.php",
    "FilipSedivy\\EET\\Exceptions\\RequirementsException" => $path["PHP-EET"] . "/src/Exceptions/RequirementsException.php",
    "FilipSedivy\\EET\\Exceptions\\ServerException"       => $path["PHP-EET"] . "/src/Exceptions/ServerException.php",


    "RobRichards\\XMLSecLibs\\XMLSecurityKey"   => $path["XMLSecLibs"] . "/src/XMLSecurityKey.php",
    "RobRichards\\XMLSecLibs\\XMLSecurityDSig"  => $path["XMLSecLibs"] . "/src/XMLSecurityDSig.php",
    "RobRichards\\XMLSecLibs\\XMLSecEnc"        => $path["XMLSecLibs"] . "/src/XMLSecEnc.php",

    "RobRichards\\WsePhp\\WSSESoap"         => $path["WSE-PHP"] . "/src/WSSESoap.php",
    "RobRichards\\WsePhp\\WSASoap"          => $path["WSE-PHP"] . "/src/WSASoap.php",
    "RobRichards\\WsePhp\\WSSESoapServer"   => $path["WSE-PHP"] . "/src/WSSESoapServer.php",
  );

  if(isset($map[$class]) && file_exists(__DIR__."/".$map[$class]))
  {
    require_once __DIR__."/".$map[$class];
  }
}

spl_autoload_register("EETLib_Autoloader");
<?php
$autoloader = ob_get_clean();

// Ukázka EET knihovny
ob_start(); ?>
require_once __DIR__."/EETLib/Autoloader.php";

use FilipSedivy\EET\Dispatcher;
use FilipSedivy\EET\Receipt;
use FilipSedivy\EET\Utils\UUID;
use FilipSedivy\EET\Certificate;

// Cesta k testovacímu certifikátu
$certExample = __DIR__."/EET_CA1_Playground-CZ00000019.p12";
$certificate = new Certificate($certExample, 'eet');

$dispatcher = new Dispatcher($certificate);
$dispatcher->setPlaygroundService();

$uuid = UUID::v4();

$r = new Receipt;
$r->uuid_zpravy = $uuid;
$r->id_provoz = '11';
$r->id_pokl = 'IP105';
$r->dic_popl = 'CZ1212121218';
$r->porad_cis = '1';
$r->dat_trzby = new \DateTime();
$r->celk_trzba = 500;

echo '<h2>---REQUEST---</h2>';
echo "<pre>";

try {

    $dispatcher->send($r);

    // Tržba byla úspěšně odeslána
    echo sprintf("FIK: %s <br>", $dispatcher->getFik());
    echo sprintf("BKP: %s <br>", $dispatcher->getBkp());

}catch(\FilipSedivy\EET\Exceptions\EetException $ex){
    // Tržba nebyla odeslána

    echo sprintf("BKP: %s <br>", $dispatcher->getBkp());
    echo sprintf("PKP: %s <br>", $dispatcher->getPkp());

}catch(Exception $ex){
    // Obecná chyba
    var_dump($ex);

}
<?php
$eetExample = ob_get_clean();

$startPhp = '<?php'.PHP_EOL;

// Detekce existence autoloaderu
write('Creating an autoloader');
if(file_exists(__DIR__.'/EETLib/Autoloader.php'))
{
    unlink(__DIR__.'/EETLib/Autoloader.php');
}
file_put_contents(__DIR__.'/EETLib/Autoloader.php', $startPhp . $autoloader);

// Detekce existence ukázky
write('Export samples');
if(file_exists(__DIR__.'/EET_Example.php'))
{
    unlink(__DIR__.'/EET_Example.php');
}
file_put_contents(__DIR__.'/EET_Example.php', $startPhp . $eetExample);

// Zkopírování příkladu
write('Export certificate');
$phpEetFolder = glob(__DIR__.'/EETLib/filipsedivy-PHP-EET*');
$eetFolderName = basename($phpEetFolder[0]);
$certExample = __DIR__.'/EETLib/'.$eetFolderName.'/examples/EET_CA1_Playground-CZ00000019.p12';
if(file_exists($certExample))
{
    if(file_exists(__DIR__.'/'.basename($certExample)))
    {
        unlink(__DIR__.'/'.basename($certExample));
    }

    copy($certExample, __DIR__.'/'.basename($certExample));
}