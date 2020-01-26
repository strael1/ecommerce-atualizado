<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


class Products extends Model {

  public static function listAll(){

  	$sql = new Sql();

  	return $sql->select("SELECT * FROM tb_products GROUP BY desproduct");

  }

  public function save(){

  	$sql = new Sql();

    $results = $sql->select("CALL sp_products_save(:idproduct,:desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl, :descricaoprod)", array(
      ":idproduct" => $this->getidproduct(),
      ":desproduct" => $this->getdesproduct(),
      ":vlprice" => $this->getvlprice(),
      ":vlwidth" => $this->getvlwidth(),
      ":vlheight" => $this->getvlheight(),
      ":vllength" => $this->getvllength(),
      ":vlweight" => $this->getvlweight(),
      ":desurl" => $this->getdesurl(),
      ":descricaoprod" => $this->getdescricaoprod()
    ));

    $this->setData($results[0]);

  }



  // Método para carregar o ID da categoria
  public function get($idproduct){

     $sql = new Sql();

     $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
      ":idproduct" => $idproduct
     ));

     $this->setData($results[0]);
  }


  // Método para deletar o ID da categoria
  public function delete()
  {

     $sql = new Sql();

     $sql->select("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
      ":idproduct" => $this->getidproduct()
     ));
      
  }

  // Criar o método checkphoto() para checar se um determinada foto existe.

  public function checkphoto()
  {

    if(file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR .
      "site" . DIRECTORY_SEPARATOR .
      "img" . DIRECTORY_SEPARATOR .
      "products" . DIRECTORY_SEPARATOR .
      $this->getidproduct() .  ".jpg"))
    {
     
      $url = DIRECTORY_SEPARATOR . "ecommerce/res/site/img/products/" . $this->getidproduct() . ".jpg";

    }else {
      
      $url = DIRECTORY_SEPARATOR . "ecommerce/res/site/img/product.jpg";
    }


    $this->setdesphoto($url);
  }


  // Criando o método checkList() para checar todos métodos criados tanto de arquivos que estão no banco
  // quanto que está dentro do método de forma dinâmica 

  public static function checkList($list){

   foreach ($list as &$row) {
      
      $p = new Products();
      $p->setData($row);
      $row = $p->getDatas();

   }

   return $list;

  }

  // Carregar os detalhes dos produtos através do método getFromURL()
  public function getFromURL($desurl){

    $sql = new Sql();

    $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
       ":desurl" => $desurl
    ]);

    $this->setData($rows[0]);
  }


  // Carregando as categorias usando o método getCategories() 

  public function getCategories(){

    $sql = new Sql();

    return $sql->select("SELECT * FROM tb_categories a INNER JOIN  tb_productscategories b
          ON a.idcategory = b.idcategory
          WHERE b.idproduct = :idproduct", [

      ":idproduct" => $this->getidproduct()
    ]);

  }





  




  // sobreescrever o método getDatas()
  public function getDatas(){
   
     $this->checkphoto();

     $value = parent::getDatas();

     return $value;

  }


  // Configurar a photo do produto 
  public function setPhoto($file){

    $extension = explode(".", $file["name"]);
    $extension = end($extension);

    switch ($extension) {
      case 'jpg':
      case 'jpge':
        $image = imagecreatefromjpeg($file["tmp_name"]);
      break;
      
      case 'gif':
        $image = imagecreatefromgif($file["tmp_name"]);
      break;

      case 'png'; 
        $image = imagecreatefrompng($file["tmp_name"]);
      break;
    }

    $dist = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR  . "res" . DIRECTORY_SEPARATOR .
      "site" . DIRECTORY_SEPARATOR .
      "img" . DIRECTORY_SEPARATOR .
      "products" . DIRECTORY_SEPARATOR .
      $this->getidproduct() .  ".jpg";

    imagejpeg($image, $dist);

    imagedestroy($image);

    $this->checkphoto();

  }


  public static function getPage($page = 1, $itemPerPage = 10){
     

     $start = ($page - 1) * $itemPerPage;

     $sql = new Sql();

     $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS * 
        FROM tb_products 
        ORDER BY desproduct
        LIMIT $start , $itemPerPage
     ");

     $resultsPage = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

     return [
       "data" => $results,
       "total" => (int)$resultsPage[0]["nrtotal"],
       "pages" => ceil($resultsPage[0]["nrtotal"]  / $itemPerPage)
     ];


  }



   public static function getSearchPage($search, $page = 1, $itemPerPage = 10){
     

     $start = ($page - 1) * $itemPerPage;

     $sql = new Sql();

     $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS * 
        FROM tb_products
        WHERE desproduct LIKE :search
        ORDER BY desproduct
        LIMIT $start , $itemPerPage
     ",  [
       ':search' => '%'. $search .'%'
     ]);

     $resultsPage = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

     return [
       "data" => $results,
       "total" => (int)$resultsPage[0]["nrtotal"],
       "pages" => ceil($resultsPage[0]["nrtotal"]  / $itemPerPage)
     ];


  }



}



 ?>