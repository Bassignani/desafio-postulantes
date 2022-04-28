<?php
/*  Se utilizo un paquete (https://github.com/chrome-php/chrome) para manejar el navegador con comando para obtener los datos deseados de la pagina. 
    Al obtener los datos del titulo y la introduccion se parsean a JSON al final del codigo.
    Al obtener los datos de la tabla se genera un arreglo para contenerlos como clave valor para luego ser parseados a JSON.
    Al ejecutar el codigo por CLI con la intruccion "php index.php" se obtienen los JSON tanto de titulo, descripcion de la tabla y la informacion de la tabla.
*/
require_once 'vendor/autoload.php';
use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory('/Applications/Google Chrome.app/Contents/MacOS/Google Chrome');

$browser = $browserFactory->createBrowser();
$url = 'https://www.sii.cl/servicios_online/1047-nomina_inst_financieras-1714.html';

try {
    $page = $browser->createPage();
    $page->navigate($url)->waitForNavigation();

    $pageTitle = $page->evaluate('document.querySelector(".title").innerText')->getReturnValue(); 
    $pageIntro = $page->evaluate('document.querySelector("p[style]").textContent')->getReturnValue(); 
    $pageTable = $page->evaluate('document.getElementById("tabledatasii").innerHTML')->getReturnValue(); 

    $tharray = [];
    $tdarray = [];
    $DataTable = [];

    preg_match_all("/<(th)>.*?<\/th>/",$pageTable,$th); 
    foreach ($th[0] as $t){
        $str= substr($t,4,-5);
        array_push($tharray,$str);
    }    

    preg_match_all("/<(td)>.*?<\/td>/",$pageTable,$td);  
    $i = 0;
    $j = 0;
    foreach ($td[0] as $t){
        $tdarray[$j][] = substr($t,4,-5);
        $i = $i + 1;
        $j = $i % count($tharray) == 0 ? $j + 1 : $j;
    }
    
    for($i = 0; $i < count($tdarray); $i++){
        for($j = 0; $j < count($tharray); $j++){
             $DataTable[$i][$tharray[$j]] = $tdarray[$i][$j];
        }
    }    
    
    $jsonTitle = json_encode($pageTitle,JSON_UNESCAPED_UNICODE);    //Contiene codificado como JSON el Titulo. 
    $jsonIntro = json_encode($pageIntro,JSON_UNESCAPED_UNICODE);    //Contiene codificado como JSON la descripcion de la Tabla.
    $jsonTable = json_encode($DataTable,JSON_UNESCAPED_UNICODE);    //Contiene codificado como JSON el contenido de la tabla.
    
   var_dump($jsonTitle);
   echo PHP_EOL;
   var_dump(json_decode($jsonIntro));
   echo PHP_EOL;
   var_dump($jsonTable);

              
 } finally {
    $browser->close();
}

?> 