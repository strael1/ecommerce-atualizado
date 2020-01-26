<?php  

namespace Hcode;

use Rain\Tpl;


class Page {
  
  private $tpl;
  private $options = [];
  private $defaults = [
    "header" => true,
    "footer" => true,
    "data" => []
  ];

  public function __construct($opts = array(), $tpl_dir = "/views/"){


   $this->options = array_merge($this->defaults, $opts);

    $config_rota_tpl = DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR;
    $config_cache_tpl = DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "views-cache" . DIRECTORY_SEPARATOR;

    $config = array(
      "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. $config_rota_tpl . $tpl_dir,
      "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]. $config_cache_tpl,
      "debug"         => false, // set to false to improve the speed
    );

    Tpl::configure( $config );
  
    $this->tpl = new Tpl;

    $this->setData($this->options["data"]);

    if($this->options["header"] === true) $this->tpl->draw("header");

  }

  private function setData($data = array()){

     
      foreach($data as $key => $value){

         $this->tpl->assign($key, $value);
      } 
  }

  public function setTpl($name, $data = array(), $returnHTML = false){
      
      $this->setData($data);
      
      $this->tpl->draw($name, $returnHTML);
      
  }   
 
  public function __destruct (){
   
    if($this->options["footer"] === true)  $this->tpl->draw( "footer" );

  }

}



// namespace Hcode;


// use Rain\Tpl;

// class Page {
  
//   private $tpl;
//   private $defaults = [
//    "data" => []
//   ];

//   private $options = [];

//   public function __construct($opts = array()) {
    
//     $this->options = array_merge($this->defaults, $opts);

//     // config
//     $config = array(
//       "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . "/views/",
//       "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
//       "debug"         => false, // set to false to improve the speed
//     );
    
//     Tpl::configure( $config );

// 	// create the Tpl object
// 	$this->tpl = new Tpl;
	

//     $this->setData($this->options["data"]);


// 	// draw the template
// 	$this->tpl->draw( "header" );

//   }

//   private function setData($data)
//   {
//       foreach ($data as $key => $value) {
      	
//       	$this->tpl->assing($key, $value);
//       }
//   }

//   public function setTpl($name, $data = array(), $returnHTML = false){
    

//     $this->setData($data);

//     $this->tpl->draw($name, $returnHTML);
//   }

//   public function __destruct(){
        
       // $this->tpl->draw("footer");
//   }

// }




?>