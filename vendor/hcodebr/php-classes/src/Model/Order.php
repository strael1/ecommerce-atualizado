<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Order extends Model {


   const SUCCESS = 'Order-Success';
   const ERROR = 'Order-Error';

   // Método para salvar os dados do usuario referente a compra como: id do usuário, status, endereço, total da compra, e o nome da compra com o endereço id da compra
   

   public function save()
   {
     
     $sql = new Sql();

     $results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
       ':idorder'=>$this->getidorder(),
       ':idcart'=>$this->getidcart(),
       ':iduser'=>$this->getiduser(),
       ':idstatus'=>$this->getidstatus(),
       ':idaddress'=>$this->getidaddress(),
       ':vltotal'=>$this->getvltotal()
     ]);
    
     if (count($results) > 0) {
        $this->setData($results[0]);
     }
  }


   public function get($idorder)
   {
   
    
    $sql = new Sql();
    
    $results = $sql->select("
      SELECT * 
      FROM tb_orders a 
      INNER JOIN tb_ordersstatus b USING(idstatus) 
      INNER JOIN tb_carts c USING(idcart)
      INNER JOIN tb_users d ON d.iduser = a.iduser
      INNER JOIN tb_addresses e USING(idaddress)
      INNER JOIN tb_persons f ON f.idperson = d.idperson
      WHERE a.idorder = :idorder
    ", [
      ':idorder'=>$idorder
    ]);

    if (count($results) > 0) {
      $this->setData($results[0]);
    }

  }

  public static function listAll(){

      $sql = new Sql();

      return $sql->select("
        SELECT * 
        FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        ORDER BY a.dtregister DESC
      ");


  }


  public function delete(){

    $sql = new Sql();

    $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder", [
      ":idorder" => $this->getidorder()
    ]);


  }

  public function getCart():Cart
  {

      $cart = new Cart();

      $cart->get((int)$this->getidcart());

      return $cart;
  }



   public static function setMsgError($msg)
   {

       $_SESSION[Order::ERROR] = $msg;
   }


   public static function getMsgError()
   {

       $msg = (isset($_SESSION[Order::ERROR])) && $_SESSION[Order::ERROR] ?  $_SESSION[Order::ERROR] : '';

       Order::clearMsgError($msg);

       return $msg;
   }


   public static function clearMsgError($msg)
   {

       $_SESSION[Order::ERROR] = NULL;
   }


   
   // Success Métodos
   public static function setSuccessError($msg)
   {

       $_SESSION[Order::SUCCESS] = $msg;
   }


   public static function getSuccessError()
   {

       $msg = (isset($_SESSION[Order::SUCCESS])) && $_SESSION[Order::SUCCESS] ?  $_SESSION[Order::SUCCESS] : '';

       Order::clearSuccessError($msg);

       return $msg;
   }


   public static function clearSuccessError($msg)
   {

       $_SESSION[Order::SUCCESS] = NULL;
   }



   public static function getPage($page = 1, $itemPerPage = 10){
     

     $start = ($page - 1) * $itemPerPage;

     $sql = new Sql();

     $results = $sql->select("
        SELECT SQL_CALC_FOUND_ROWS * 
        FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        ORDER BY d.dtregister DESC
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
        FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
        WHERE a.idorder = :id OR f.desperson LIKE :search
        ORDER BY a.dtregister DESC
        LIMIT $start , $itemPerPage
     ",  [
       ':search' => '%'. $search .'%',
       ':id' => $search
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