<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;
use \Hcode\Model\Address;
use \Hcode\Model\Cart;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {

   $product = Products::listAll();

   $page = new Page();

   $page->setTpl("index", [
     "products" => Products::checkList($product)
   ]);

});


// Configurando a rota dos detalhes dos produtos
$app->get("/products/:desurl", function ($desurl){

  $product = new Products();
 
  $product->getFromURL($desurl);

  $page = new Page();

  $page->setTpl("product-detail", [
    "product" => $product->getDatas(),
    "categories" => $product->getCategories()
  ]);

});



// Configurando a rota do carrinho 
$app->get('/cart', function (){

  $cart = Cart::getFromSession(); 

  $page = new Page();

  $page->setTpl("cart", [
   "cart" =>$cart->getDatas(),
   "products" => $cart->getProducts(),
   "error" => Cart::getMsgError()
  ]); 
});


// Configurando rota de adicionar o carrinho 
$app->get("/cart/:idproduct/add", function ($idproduct){

  $product = new Products();

  $product->get((int)$idproduct);

  $cart = Cart::getFromSession();

  $qtd = (isset($_GET["qtd"])) ? $_GET["qtd"] : 1;

  for($i = 0; $i < $qtd; $i ++){
     
     $cart->addProduct($product);
    
  }

  header("Location: /ecommerce/cart");
  exit;

});





// Configurando rota de diminuir quandidade de produto no carrinho 
$app->get("/cart/:idproduct/minus", function ($idproduct){

  $product = new Products();

  $product->get((int)$idproduct);

  $cart = Cart::getFromSession();

  $cart->removeProduct($product);

  header("Location:  /ecommerce/cart");
  exit;

});


// Configurando rota de remove todos os produtos do carrinho 
$app->get("/cart/:idproduct/remove", function ($idproduct){

  $product = new Products();

  $product->get((int)$idproduct);

  $cart = Cart::getFromSession();

  $cart->removeProduct($product, true);

  header("Location:  /ecommerce/cart");
  exit;

});




// Enviar o cep para calcular o frete.
$app->post("/cart/freight", function (){
  
  $cart = Cart::getFromSession();

  $cart->setFreight($_POST["zipcode"]);

  header("Location: /ecommerce/cart");
  exit;

});



// Checkout rota
$app->get("/checkout", function (){

 User::verifyLogin(false);

 $address = new Address();

 $cart = Cart::getFromSession();
 

 if(!isset($_GET["zipcode"])){

    $_GET["zipcode"] = $cart->getdeszipcode();

 }


 if(isset($_GET["zipcode"])){
 
    $address->loadFromCEP($_GET["zipcode"]);

    $cart->setdeszipcode($_GET["zipcode"]);

    $cart->save();

    $cart->getCalculateTotal();

 }


 if(!$address->getdesaddress()) $address->setdesaddress('');
 if(!$address->getdesnumber()) $address->setdesnumber('');
 if(!$address->getdescomplement()) $address->setdescomplement('');
 if(!$address->getdesdistrict()) $address->setdesdistrict('');
 if(!$address->getdescity()) $address->setdescity('');
 if(!$address->getdesstate()) $address->setdesstate('');
 if(!$address->getdescountry()) $address->setdescountry('');
 if(!$address->getdeszipcode()) $address->setdeszipcode('');


 $page = new Page();

 $page->setTpl("checkout", [
   "cart" => $cart->getDatas(),
   "address" => $address->getDatas(),
   "products" => $cart->getProducts(),
   "error" => Address::getMsgError()
 ]);

});




// /POST --> /checkout
$app->post("/checkout", function (){

  User::verifyLogin(false);


  if(!isset($_POST["zipcode"]) || $_POST["zipcode"] === ''){

     Address::setMsgError("Informe o CEP!");
     header("Location: /ecommerce/checkout");
     exit;

  }

  if(!isset($_POST["desaddress"]) || $_POST["desaddress"] === ''){

     Address::setMsgError("Informe o Endereço!");
     header("Location: /ecommerce/checkout");
     exit;
  }

  if(!isset($_POST["desdistrict"]) || $_POST["desdistrict"] === ''){

     Address::setMsgError("Informe o seu bairro!");
     header("Location: /ecommerce/checkout");
     exit;

  }

  if(!isset($_POST["descity"]) || $_POST["descity"] === ''){

     Address::setMsgError("Informe sua cidade!");
     header("Location: /ecommerce/checkout");
     exit;
  }

  if(!isset($_POST["desstate"]) || $_POST["desstate"] === ''){

     Address::setMsgError("Informe o seu estado!");
     header("Location: /ecommerce/checkout");
     exit;
  }

  if(!isset($_POST["descountry"]) || $_POST["descountry"] === ''){

     Address::setMsgError("Informe o seu país!");
     header("Location: /ecommerce/checkout");
     exit;
  }

  $user = User::getFromSession();

  $address = new Address();

  $_POST["deszipcode"] = $_POST["zipcode"];
  $_POST["idperson"] = $user->getidperson();


  $address->setData($_POST);


  $address->save();


  $cart = Cart::getFromSession();

  $cart->getCalculateTotal();

  $order = new Order();


  $order->setData([
    'idcart'=>$cart->getidcart(),
    'idaddress'=>$address->getidaddress(),
    'iduser'=>$user->getiduser(),
    'idstatus'=>OrderStatus::EM_ABERTO,
    'vltotal'=>$cart->getvltotal()
  ]);

  $order->save();

  header("Location: /ecommerce/order/".$order->getidorder() . '/pagseguro');
  exit;

});




// Rota para finalizar o pagamento 
$app->get('/order/:idorder', function ($idorder){
   
   User::verifyLogin(false);

   $order = new Order();

   $order->get((int)$idorder);

   $page = new Page();

   $page->setTpl('payment', [
     "order" => $order->getDatas()
   ]);
});



// Rota para criação do boleto
$app->get('/boleto/:idorder', function ($idorder){

  User::verifyLogin(false);

  $order = new Order();

  $order->get((int)$idorder);


  // DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 5;
$taxa_boleto = 2.95;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(".", "", $valor_cobrado);
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
$dadosboleto["nosso_numero"] = $order->getidorder();
$dadosboleto["numero_documento"] = $order->getidorder();  // Num do pedido ou do documento
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto;   // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $order->getdesperson();
$dadosboleto["endereco1"] = $order->getdesaddress() . " " .  $order->getdesdistrict();
$dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Mixpreço";
$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
$dadosboleto["demonstrativo3"] = "";
// INSTRUÇÕES PARA O CAIXA
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@mixpreco.com.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Loja mixpreco - www.mixpreco.com.br";
// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";    
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "";
// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
// DADOS DA SUA CONTA - BANCO DO BRASIL
$dadosboleto["agencia"] = "9999"; // Num da agencia, sem digito
$dadosboleto["conta"] = "99999";  // Num da conta, sem digito
// DADOS PERSONALIZADOS - BANCO DO BRASIL
$dadosboleto["convenio"] = "7777777";  // Num do convênio - REGRA: 6 ou 7 ou 8 dígitos
$dadosboleto["contrato"] = "999999"; // Num do seu contrato
$dadosboleto["carteira"] = "18";
$dadosboleto["variacao_carteira"] = "-019";  // Variação da Carteira, com traço (opcional)
// TIPO DO BOLETO
$dadosboleto["formatacao_convenio"] = "7"; // REGRA: 8 p/ Convênio c/ 8 dígitos, 7 p/ Convênio c/ 7 dígitos, ou 6 se Convênio c/ 6 dígitos
$dadosboleto["formatacao_nosso_numero"] = "2"; // REGRA: Usado apenas p/ Convênio c/ 6 dígitos: informe 1 se for NossoNúmero de até 5 dígitos ou 2 para opção de até 17 dígitos
/*
#################################################
DESENVOLVIDO PARA CARTEIRA 18
- Carteira 18 com Convenio de 8 digitos
  Nosso número: pode ser até 9 dígitos
- Carteira 18 com Convenio de 7 digitos
  Nosso número: pode ser até 10 dígitos
- Carteira 18 com Convenio de 6 digitos
  Nosso número:
  de 1 a 99999 para opção de até 5 dígitos
  de 1 a 99999999999999999 para opção de até 17 dígitos
#################################################
*/
// SEUS DADOS
$dadosboleto["identificacao"] = "Mixpreço";
$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
$dadosboleto["cidade_uf"] = "Recife / PE";
$dadosboleto["cedente"] = "Mixpreço LTDA - ME";
// NÃO ALTERAR!
$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
 
  require_once($path . "funcoes_bb.php");
  require_once($path . "layout_bb.php");

});





$app->get('/order/:idorder/pagseguro', function ($idorder){

  User::verifyLogin(false);

  $order = new Order();

  $order->get((int)$idorder);

  $cart = $order->getCart();

  $page = new Page([
     "header" => false,
     "footer" => false
  ]);

  $page->setTpl('payment-pagseguro', [
    'order' => $order->getDatas(),
    'cart' => $cart->getDatas(),
    'products' => $cart->getProducts(),
    'phone' => [
      'areaCode'=> substr($order->getnrphone(), 0, 2),
      'number' => substr($order->getnrphone(), 2, strlen($order->getnrphone()))
    ]
  ]);
});



// rota para as orders do perfil
$app->get('/profile/orders', function(){
   
   User::verifyLogin(false);

   $user = User::getFromSession();

   $page = new Page();

   $page->setTpl('profile-orders',[
     "orders" => $user->getOrders()
   ]); 
});



// rota para as orders do perfil idorder 
$app->get('/profile/orders/:idorder', function($idorder){
   
   User::verifyLogin(false);

   $order = new Order();

   $order->get((int)$idorder);

   $cart = new Cart();

   $cart->get((int)$order->getidcart());

   $cart->getCalculateTotal();

   $page = new Page();

   $page->setTpl("profile-orders-detail", [
      "order" => $order->getDatas(),
      "cart" => $cart->getDatas(),
      "products" => $cart->getProducts()
   ]);
});








//Registro do usuário, ou cadastro do usuário
$app->post("/register", function (){

  $_SESSION["registerValues"] = $_POST;


  if(!isset($_POST["name"]) || $_POST["name"] === ''){
    
    User::setErroRegister("Preencha o seu nome");

    header("Location: /ecommerce/login");
    exit;
  }



  if(!isset($_POST["email"]) || $_POST["email"] === ''){
    
    User::setErroRegister("Preencha o seu e-mail");

    header("Location: /ecommerce/login");
    exit;
  }


  if(!isset($_POST["password"]) || $_POST["password"] === ''){
    
    User::setErroRegister("Preencha a sua senha");

    header("Location: /ecommerce/login");
    exit;
  }


  if(User::checkLoginExist($_POST["email"]) === true){
  
    User::setErroRegister("Conta e e-mail já esta sendo usado por outro usuário.");

    header("Location: /ecommerce/login");
    exit;

  }
 

  $user = new User();

  $user->setData([
    'inadmin' => 0,
    'deslogin' => $_POST["email"],
    'desperson' => $_POST["name"],
    'desemail' => $_POST["email"],
    'despassword' => $_POST["password"],
    'nrphone' => $_POST["phone"] 
  ]);

  $user->save();

  User::login($_POST['email'], $_POST['password']); 

  header("Location: /ecommerce/checkout");
  exit;

});










// Rota do login do usuário // GET
$app->get("/login", function (){

  $page = new Page();

  $page->setTpl("login", [
    "error" => User::getMsgError(),  
    "errorRegister" => User::getErroRegister(),
    "registerValues" => (isset($_SESSION["registerValues"])) ? $_SESSION["registerValues"] : ['name' => '','email' => '', 'phone' => '']
  ]);

});

// Rota do login do usuário // POST
$app->post("/login", function (){

  try {

    User::login($_POST["login"],$_POST["password"]);
    
  } catch(Exception $e){
   
     throw new Exception("E-mail ou senha inválido");
     
  }

  header("Location: /ecommerce/checkout");
  exit;

});








// Criando a rota de sair 
$app->get("/logout", function (){
  
  User::logout();

  header("Location: /ecommerce/login");
  exit;

});








$app->get("/admin", function(){
  
    User::verifyLogin();

    $pageAdmin = new PageAdmin();

    $pageAdmin->setTpl("index");
});


$app->get("/admin/login", function (){

	$pageAdmin = new PageAdmin([
     "header" => false,
     "footer" => false
	]);

	$pageAdmin->setTpl("login");
});

$app->get('/admin/logout', function (){

    User::logout();

    header("Location: /ecommerce/admin/login");
    exit;
});

$app->post("/admin/login", function(){
 
  User::login($_POST["login"], $_POST["password"]);
  header("Location: /ecommerce/admin");
  exit;

});

// Cria a rota dos usuários
$app->get("/admin/users", function (){
   
   // Criar um User::listAll(); e armazenar na variável $user   
   User::verifyLogin();

   $search = (isset($_GET["search"])) ? $_GET["search"] : '';
   $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;


   if($search != ''){

      $pagination = User::getSearchPage($search, $page);

   }else {
 
      $pagination = User::getPage($page);     
   }

   $pages = [];

   for ($x = 0; $x < $pagination['pages']; $x++) { 
     
       array_push($pages, [
          'href' => '/ecommerce/admin/users?' . http_build_query([
               'page' => $x + 1,
               'search' => $search
          ]),
          'text' => $x + 1
       ]);
   }

   $page = new PageAdmin();
   
   $page->setTpl("users", array(
     "users" => $pagination['data'],
     "search" => $search,
     "pages" => $pages
   ));

});



// Rota que renderiza o template para trocar a senha do usuário
$app->get('/admin/users/:iduser/password', function ($iduser){
   
   User::verifyLogin();

   $user = new User();

   $user->get((int)$iduser);

   $page = new PageAdmin();

   $page->setTpl('users-password',[
     "user" => $user->getDatas(),
     "msgError" => $user->getMsgError(),
     "msgSuccess" => $user->getSuccessError()
   ]);

});


// Rota que envia um post para trocar a senha do usuário
$app->post('/admin/users/:iduser/password', function ($iduser){

   User::verifyLogin();

   if(!isset($_POST["despassword"]) || $_POST["despassword"] === ''){

      User::setMsgError('Preencha a senha!');
      header("Location: /ecommerce/admin/users/$iduser/password");
      exit;
   }


   if(!isset($_POST["despassword-confirm"]) || $_POST["despassword-confirm"] === ''){

      User::setMsgError('Confirme a senha!');
      header("Location: /ecommerce/admin/users/$iduser/password");
      exit;
   }

   if($_POST["despassword"] !== $_POST["despassword-confirm"]){

      User::setMsgError("Senha diferente! por favor confime a senha.");
      header("Location: /ecommerce/admin/users/$iduser/password");
      exit;
   }


   $user = new User();

   $user->get((int)$iduser);

   $user->setPassword(User::getPasswordHash($_POST["despassword"]));

   $user->setSuccessError('Senha alterada com sucesso!');

   header("Location: /ecommerce/admin/users/$iduser/password");
   exit;
});











// Adiciona o template da criação do usuário
$app->get("/admin/users/create", function (){
   
   User::verifyLogin();

   $pageAdmin = new PageAdmin();
   $pageAdmin->setTpl("users-create");
});


$app->get("/admin/users/:iduser/delete", function ($iduser){

  User::verifyLogin();

  $user = new User();

  $user->get((int)$iduser);

  $user->delete();

  header("Location: /ecommerce/admin/users");
  exit;
});


// Adiciona o template update do usuário
$app->get("/admin/users/:iduser", function ($iduser){
   
   User::verifyLogin();

   $user = new User();

   $user->get((int)$iduser);

   $pageAdmin = new PageAdmin();

   $pageAdmin->setTpl("users-update", array(
     "user" => $user->getDatas()
   ));
});




// Cria o usuário 
$app->post("/admin/users/create", function (){
   
   User::verifyLogin();

   $user = new User();

   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

   $_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

    "cost"=>12

   ]);

   $user->setData($_POST);

   $user->save();

   header("Location: /ecommerce/admin/users");
   exit;

});

// Atualiza o usuário
$app->post("/admin/users/:iduser", function ($iduser){
   
   User::verifyLogin();

   $user = new User();

   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

   $user->get((int)$iduser);

   $user->setData($_POST);

   $user->update();

   header("Location: /ecommerce/admin/users");
   exit;


});


// Esqueceu a senha na parte a administração
$app->get("/admin/forgot", function (){
    
  $page = new PageAdmin([
    "footer" => false,
    "header" => false
  ]);

  $page->setTpl("forgot");
});



$app->post("/admin/forgot", function (){

  $user = User::getForgot($_POST["email"]);

  header("Location: /ecommerce/admin/forgot/sent");
  exit;
});



$app->get("/admin/forgot/sent", function (){

   $page = new PageAdmin([
    "footer" => false,
    "header" => false
  ]);

  $page->setTpl("forgot-sent");

});



$app->get("/admin/forgot/reset", function (){

  $user = User::validForgotDecrypt($_GET["code"]);
  
  $page = new PageAdmin([
    "footer" => false,
    "header" => false
  ]);

  $page->setTpl("forgot-reset", array(
    "name" => $user["desperson"],
    "code" => $_GET["code"]
  ));

});



$app->post("/admin/forgot/reset", function (){

  $forgot = User::validForgotDecrypt($_POST["code"]);

  User::setForgotUsed($forgot["idrecovery"]);

  $user = new User();

  $user->get((int)$forgot["iduser"]);

  $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
     "cost" => 12
  ]);

  $user->setPassword($password);


  $page = new PageAdmin([
    "footer" => false,
    "header" => false
  ]);

  $page->setTpl("forgot-reset-success");


});









// Esqueceu a senha na parte do usuário
$app->get("/forgot", function() {

  $page = new Page();
  $page->setTpl("forgot");  

});


$app->post("/forgot", function(){

  $user = User::getForgot($_POST["email"], false);

  header("Location: /ecommerce/forgot/sent");
  exit;
}); 


$app->get("/forgot/sent", function(){

  $page = new Page();
  $page->setTpl("forgot-sent"); 

});


$app->get("/forgot/reset", function(){
  
  $user = User::validForgotDecrypt($_GET["code"]);
  
  $page = new Page();
  
  $page->setTpl("forgot-reset", array(
    "name"=>$user["desperson"],
    "code"=>$_GET["code"]
  ));

});




$app->post("/forgot/reset", function(){
  
  $forgot = User::validForgotDecrypt($_POST["code"]); 
  
  User::setForgotUsed($forgot["idrecovery"]);
  
  $user = new User();
  
  $user->get((int)$forgot["iduser"]);
  
  $password = User::getPasswordHash($_POST["password"]);
  
  $user->setPassword($password);
  
  $page = new Page();
  
  $page->setTpl("forgot-reset-success");

});




// Área do perfil do usuário
$app->get("/profile", function (){

  User::verifyLogin(false);

  $user = User::getFromSession();

  $page = new Page();

  $page->setTpl("profile", [
    'user' => $user->getDatas(),
    'profileMsg' => User::getSuccessError(),
    'profileError' => User::getMsgError()
  ]);

});

$app->post("/profile", function (){

  User::verifyLogin(false);

  if(!isset($_POST["desperson"]) || $_POST["desperson"] === ''){

     User::setMsgError("Preencha o nome!");
     header("Location: /ecommerce/profile");
     exit;
  }


  if(!isset($_POST["desemail"]) || $_POST["desemail"] === ''){
 
     User::setMsgError("Preencha o E-mail!");
     header("Location: /ecommerce/profile");
     exit;
  }

  $user = User::getFromSession();


  if($_POST["desemail"] !== $user->getdesemail()){

    if(User::checkLoginExist($_POST["desemail"])){

       User::setMsgError("O e-mail já está cadastrado!");
       header("Location: /ecommerce/profile");
       exit;
    }

  }


  $_POST["inadmin"] = $user->getinadmin();
  $_POST["despassword"] = $user->getdespassword();
  $_POST["deslogin"] = $_POST["desemail"];

  $user->setData($_POST);

  $user->save();

  User::setSuccessError('Dados alterados com sucesso!');

  header("Location: /ecommerce/profile");
  exit;

});

$app->get('/profile/change-password', function(){
 
   User::verifyLogin(false);

   $user = User::getFromSession();

   $page = new Page();

   $page->setTpl("profile-change-password", [
     'changePassError' => User::getMsgError(),
     'changePassSuccess' => User::getSuccessError()
   ]);
});


$app->post('/profile/change-password', function(){

   User::verifyLogin(false);


   if(!isset($_POST["current_pass"]) || $_POST["current_pass"] === ''){

       User::setMsgError('Digite a senha atual');
       header('Location: /ecommerce/profile/change-password');
       exit;
   }

   if(!isset($_POST["new_pass"]) || $_POST["new_pass"] === ''){

       User::setMsgError('Digite a nova senha');
       header('Location: /ecommerce/profile/change-password');
       exit;
   }

   if(!isset($_POST["new_pass_confirm"]) || $_POST["new_pass_confirm"] === ''){

       User::setMsgError('Confirme a nova senha');
       header('Location: /ecommerce/profile/change-password');
       exit;
   }

   if($_POST["current_pass"] === $_POST["new_pass"]){
      
      User::setMsgError('As senhas não pode ser iguais, digite uma senha diferente.');
      header('Location: /ecommerce/profile/change-password');
      exit;
   }


   $user = User::getFromSession();

   if(!password_verify($_POST["current_pass"], $user->getdespassword())){

      User::setMsgError('As senhas está inválida.');
      header('Location: /ecommerce/profile/change-password');
      exit;
   }

   $user->setdespassword($_POST["new_pass"]);

   $user->update();

   User::setSuccessError('Senha alterada com sucesso!');

   header("Location: /ecommerce/profile/change-password");
   exit;

});



// current_pass
// new_pass
// new_pass_confirm



$app->get("/admin/categories", function (){
   
   User::verifyLogin();

   $search = (isset($_GET["search"])) ? $_GET["search"] : '';
   $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;


   if($search != ''){

      $pagination = Category::getSearchPage($search, $page);

   }else {
 
      $pagination = Category::getPage($page);     
   }

   $pages = [];

   for ($x = 0; $x < $pagination['pages']; $x++) { 
     
       array_push($pages, [
          'href' => '/ecommerce/admin/categories?' . http_build_query([
               'page' => $x + 1,
               'search' => $search
          ]),
          'text' => $x + 1
       ]);
   }

   $page = new PageAdmin();

   $page->setTpl("categories", array(
     "categories" => $pagination['data'],
     "search" => $search,
     "pages" => $pages
   ));

});



$app->get("/admin/categories/create", function (){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("categories-create");
});



// Criando o post do create categories para criar e salvar 
$app->post("/admin/categories/create", function (){
   
  User::verifyLogin();

  // instânciar a categoria 
  $category = new Category();

  // setar o post 
  $category->setData($_POST);

  // salvar a categoria 
  $category->save();

  header("Location: /ecommerce/admin/categories");
  exit;

});



// Rota de delete
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

   User::verifyLogin();

   $category = new Category();

   $category->get((int)$idcategory);   

   $category->delete();

   header("Location:  /ecommerce/admin/categories");
   exit;

});



// Rota para atualizar as categorias
$app->get("/admin/categories/:idcategory", function($idcategory){
    
   User::verifyLogin();

   $category = new Category();
   
   $category->get((int)$idcategory);

   $page = new PageAdmin();

   $page->setTpl("categories-update", [
     "category" => $category->getDatas()
   ]);

});


// Rota para salva a alteração
$app->post("/admin/categories/:idcategory", function($idcategory){
    
   User::verifyLogin();

   $category = new Category();
   
   $category->get((int)$idcategory);

   $category->setData($_POST);

   $category->save();

   header("Location: /ecommerce/admin/categories");
   exit;

});


// Acessa os produtos pelo o seu número de ID
$app->get("/admin/categories/:idproduct/products", function($idproduct){
  
  User::verifyLogin();

  $category = new Category();

  $category->get((int)$idproduct);

  $page = new PageAdmin();

  $page->setTpl("categories-products", [
    "category" => $category->getDatas(),
    'productsRelated' => $category->getProduct(),
    'productsNotRelated' => $category->getProduct(false)
  ]);



});


// Adiciona um produto a categoria pelo seu ID
$app->get("/admin/categories/:category/products/:idproduct/add", function ($idcategory,$idproduct){

  $category = new Category();

  $category->get((int)$idcategory);

  $product = new Products();

  $product->get((int)$idproduct);

  $category->addProduct($product);

  header("Location:  /ecommerce/admin/categories/" . $idcategory . "/products");
  exit;

});


// Remove um produto pelo o seu ID
$app->get("/admin/categories/:category/products/:idproduct/remove", function ($idcategory,$idproduct){

  $category = new Category();

  $category->get((int)$idcategory);

  $product = new Products();

  $product->get((int)$idproduct);

  $category->removeProduct($product);

  header("Location:  /ecommerce/admin/categories/" . $idcategory . "/products");
  exit;

});




$app->get("/category/:idcategory", function ($idcategory){

  $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

  $category = new Category();

  $category->get((int)$idcategory);

  $pagination = $category->getProductsPage($page);

  $pages = [];

  for ($i=1; $i <= $pagination["pages"]; $i++) { 
     array_push($pages, [
       "link" => "/category/" . $category->getidcategory() . "?page=" . $i,
       "page" => $i
     ]);
  }

  $page = new Page();

  $page->setTpl("category", [
    "category" => $category->getDatas(),
    "products" => $pagination["data"],
    "pages" => $pages
  ]);


});


// GET/ Rota para produtos
$app->get('/admin/products', function (){
    
   User::verifyLogin();

   $search = (isset($_GET["search"])) ? $_GET["search"] : '';
   $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;


   if($search != ''){

      $pagination = Products::getSearchPage($search, $page);

   }else {
 
      $pagination = Products::getPage($page);     
   }

   $pages = [];

   for ($x = 0; $x < $pagination['pages']; $x++) { 
     
       array_push($pages, [
          'href' => '/ecommerce/admin/products?' . http_build_query([
               'page' => $x + 1,
               'search' => $search
          ]),
          'text' => $x + 1
       ]);
   }


  

   $page = new PageAdmin();

   $page->setTpl("products", [
     "products" => $pagination['data'],
     "search" => $search,
     "pages" => $pages
   ]);
});


// GET/ rota para a criação do produto
$app->get("/admin/products/create", function (){

  User::verifyLogin();

  $page = new pageAdmin();

  $page->setTpl("products-create");

});

// POST/ rota para a criação do produto.
$app->post("/admin/products/create", function (){
  
 User::verifyLogin();

 $product = new Products();

 $product->setData($_POST);

 $product->save();

 header("Location: /ecommerce/admin/products");
 exit;


});


$app->get("/admin/products/:idproduct", function ($idproduct){
 
  User::verifyLogin();  

  $product = new Products();
  
  $product->get((int)$idproduct);

  $page = new pageAdmin();

  $page->setTpl("products-update", [
    "product" => $product->getDatas()
  ]);

});


$app->post("/admin/products/:idproduct", function ($idproduct){
 
  User::verifyLogin();  

  $product = new Products();
  
  $product->get((int)$idproduct);

  $product->get((int)$idproduct);

  $product->setData($_POST);

  $product->setPhoto($_FILES["file"]);

  header("Location: /ecommerce/admin/products");
  exit;

});



$app->get("/admin/products/:idproduct/delete", function ($idproduct){
  
  User::verifyLogin();

  $product = new Products();

  $product->get((int)$idproduct);

  $product->delete();

  header("Location: /ecommerce/admin/products");
  exit;

});





// Rota para os status se está em aberto ou em andamento ou pago
$app->get("/admin/orders/:idorder/status", function($idorder){
  
   User::verifyLogin();

   $order = new Order();

   $order->get((int)$idorder);

   $page = new PageAdmin();

   $page->setTpl('order-status', [
      "order" => $order->getDatas(),
      "status" => OrderStatus::listAll(),
      'msgError' => Order::getMsgError(),
      'msgSuccess' => Order::getSuccessError()
    ]);

});




$app->post('/admin/orders/:idorder/status', function ($idorder){

  User::verifyLogin();

  if(!isset($_POST["idstatus"]) || !(int)$_POST["idstatus"] > 0){

     Order::setMsgError('Informe o status atual');
     header('Location: /ecommerce/admin/orders/' . $idorder . '/status');
     exit;

  }

  $order = new Order();

  $order->get((int)$idorder);

  $order->setidstatus((int)$_POST["idstatus"]);

  $order->save();

  $order->setSuccessError('status Atualizado!');

  header('Location: /ecommerce/admin/orders/' . $idorder . '/status');
  exit;

});



// Rota para excluir o pedido 
$app->get('/admin/orders/:idorder/delete', function ($idorder){

   User::verifyLogin();
  
   $order = new Order();

   $order->get((int)$idorder);

   $order->delete();

   header('Location: /ecommerce/admin/orders');
   exit;

});



// Rota para os detalhes dos produtos
$app->get('/admin/orders/:idorder', function ($idorder){
   
    User::verifyLogin();

    $order = new Order();

    $order->get((int)$idorder);


    $cart = $order->getCart();

    $page = new PageAdmin();

    $page->setTpl('order', [
      "order" => $order->getDatas(),
      "cart" => $cart->getDatas(),
      "products" => $cart->getProducts()
    ]);
});




// Admin orders Page
$app->get('/admin/orders', function (){

   User::verifyLogin();


   $search = (isset($_GET["search"])) ? $_GET["search"] : '';
   $page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;


   if($search != ''){

      $pagination = Order::getSearchPage($search, $page);

   }else {
 
      $pagination = Order::getPage($page);     
   }

   $pages = [];

   for ($x = 0; $x < $pagination['pages']; $x++) { 
     
       array_push($pages, [
          'href' => '/ecommerce/admin/categories?' . http_build_query([
               'page' => $x + 1,
               'search' => $search
          ]),
          'text' => $x + 1
       ]);
   }



   $page = new PageAdmin();

   $page->setTpl('orders', [
     "orders" => $pagination['data'],
     "search" => $search,
     "pages" => $pages
   ]);
});





















// Função para formatar o preço dos produtos.
function formatPrice($vlprice){
  
  if($vlprice == true)
   (float)$vlprice;
   return number_format($vlprice, 2, "," , ".");

}


// Checando se o login é verdadeiro
function checkLogin($inadmin = true){

  return User::checkLogin($inadmin);

}


// Checando a sessão da desperson do usuário
function getUserName()
{
  $user = User::getFromSession();

  return $user->getdeslogin();
}



// Função que calcula a quantidade de itens que está dentro do carrinho
function getCartNrQtd(){
  
  $cart = Cart::getFromSession();

  $totals = $cart->getProductsTotals();

  return $totals['nrqtd'];  

}

// Função que calcula a quantidade mas sem o carrinho
function getCartVlSubtotal(){
  
  $cart = Cart::getFromSession();

  $totals = $cart->getProductsTotals();

  return formatPrice($totals['vlprice']);  

}


// Função para formatar a data 
function formatData($date){

  return date('d/m/Y', strtotime($date));

}







$app->run();

 ?>



 