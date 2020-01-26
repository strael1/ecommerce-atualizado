<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

  public static function listAll(){

  	$sql = new Sql();

  	return $sql->select("SELECT * FROM tb_categories GROUP BY descategory");

  }

  public function save(){

  	$sql = new Sql();

  	$results = $sql->select("CALL sp_categories_save(:idcategory,:descategory)", array(
      ":idcategory" => $this->getidcategory(),
      ":descategory" => $this->getdescategory()
  	));

  	$this->setData($results[0]);

  	 Category::updateFile();
  }


  // Método para carregar o ID da categoria
  public function get($idcategory){

     $sql = new Sql();

     $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
      ":idcategory" => $idcategory
     ));

     $this->setData($results[0]);
  }


  // Método para deletar o ID da categoria
  public function delete()
  {

     $sql = new Sql();

     $sql->select("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
      ":idcategory" => $this->getidcategory()
     ));

     Category::updateFile();
      
  }

  public static function updateFile()
  {


  	 $categories = Category::listAll();

  	 $html = [];

  	 foreach ($categories as $row) {
  	 	array_push($html,'<li><a href="/ecommerce/category/'.$row["idcategory"].'">' . $row["descategory"] . '</a></li>');
  	 }

     file_put_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "ecommerce" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html));


  }

  public function getProduct($related = true){

    $sql = new Sql();

    if($related === true){

        return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
           SELECT a.idproduct 
           FROM tb_products a  
           INNER JOIN tb_productscategories b
           ON a.idproduct = b.idproduct
           WHERE b.idcategory = :idcategory
          )
        ", array(
          ":idcategory" => $this->getidcategory() 
        ));

    }else {
        return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
           SELECT a.idproduct 
           FROM tb_products a  
           INNER JOIN tb_productscategories b
           ON a.idproduct = b.idproduct
           WHERE b.idcategory = :idcategory
          )
        ", array(
          ":idcategory" => $this->getidcategory() 
        ));

    }


  }

  public function addProduct(Products $product){


     $sql = new Sql();

     $sql->select("INSERT INTO tb_productscategories(idcategory, idproduct) VALUES(:idcategory,:idproduct)", array(
       ":idcategory" => $this->getidcategory(),
       ":idproduct" => $product->getidproduct()
     ));
  }

  public function removeProduct(Products $product){

     $sql = new Sql();

     $sql->select("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
      ":idcategory" => $this->getidcategory(),
       ":idproduct" => $product->getidproduct()
     ));
  }

  public function getProductsPage($page = 1, $itemPerPage = 8){
     

     $start = ($page - 1) * $itemPerPage;

     $sql = new Sql();

     $results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * 
        FROM tb_products a 
        INNER JOIN tb_productscategories b 
        ON a.idproduct = b.idproduct
        INNER JOIN tb_categories c
        ON b.idcategory = c.idcategory
        WHERE c.idcategory = :idcategory
        LIMIT $start , $itemPerPage", array(
         ":idcategory" => $this->getidcategory()
        ));

     $resultsPage = $sql->select("SELECT FOUND_ROWS() AS nrtotal;
");

     return [
       "data" => Products::checkList($results),
       "total" => (int)$resultsPage[0]["nrtotal"],
       "pages" => ceil($resultsPage[0]["nrtotal"]  / $itemPerPage)
     ];


  }


  public static function getPage($page = 1, $itemPerPage = 10){
     

     $start = ($page - 1) * $itemPerPage;

     $sql = new Sql();

     $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS * 
        FROM tb_categories
        ORDER BY descategory
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
        FROM tb_categories
        WHERE descategory LIKE :search
        ORDER BY descategory
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