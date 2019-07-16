<?php

use \virtualstore\Page;
use \virtualstore\Model\Category;
use \virtualstore\Model\Product;
use \virtualstore\Model\Cart;
use \virtualstore\Model\Address;
use \virtualstore\Model\User;

$app->get('/', function() {

	$products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);

});

$app->get('/categories/:idcategory', function($idcategory) {

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;

	$category = new Category();
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination["pages"]; $i++) { 
		array_push($pages, [
			"link"=>"/categories/".$category->getidcategory()."?page=".$i,
			"page"=>$i
		]);
	}

	$page = new Page();
	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);

});

$app->get('/products/:desurl', function ($desurl) {
	
	$product = new Product();

	$product->getFromUrl($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		"product" => $product->getValues(),
		"categories" => $product->getCategories()
	]);

});

$app->get('/cart', function () {

	$cart = Cart::getFromSession();

	$cart->checkZipCode();
	
	$page = new Page();

	$page->setTpl("cart", [
		"cart" => $cart->getValues(),
		"products" => $cart->getProducts(),
		"error" => $cart::getMsgError()
	]);

});

$app->get('/cart/:idproduct/add', function ($idproduct) {
	
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		$cart->addProduct($product);
	}	

	header("Location: /cart");
	exit;

});

$app->get('/cart/:idproduct/minus', function ($idproduct) {
	
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

$app->get('/cart/:idproduct/remove', function ($idproduct) {
	
	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

$app->post('/cart/freight', function () {
	
	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;

});

$app->get('/checkout', function () {

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();
	
	$page = new Page();

	$page->setTpl('checkout', [
		'cart' => $cart->getValues(),
		'address' => $address->getValues()
	]);

});

$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error' => User::getError(),
		'registerError' => User::getRegisterError(),
		'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);

});

$app->post("/login", function(){

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch(Exception $e) {

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

$app->get('/logout', function () {

	User::logout();

	header("Location: /");
	exit;

});

$app->post("/register", function(){

	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == '') {

		User::setRegisterError("Preencha seu nome");

		header("Location: /login");
		exit;

	}

	if(!isset($_POST['email']) || $_POST['email'] == '') {

		User::setRegisterError("Preencha seu email");

		header("Location: /login");
		exit;

	}
	
	if(!isset($_POST['password']) || $_POST['password'] == '') {

		User::setRegisterError("Preencha a senha");

		header("Location: /login");
		exit;

	}

	if (User::checkLoginExists($_POST['email']) === true ) {
		
		User::setRegisterError("Este endereço de email já está sendo usado");

		header("Location: /login");
		exit;

	}

	$user = new User();

	$user->setData([
		'inadmin' => 0,
		'deslogin' => $_POST['email'],
		'desperson' => $_POST['name'],
		'desemail' => $_POST['email'],
		'nrphone' => $_POST['phone'],
		'despassword' => $_POST['password'],
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header("Location: /checkout");
	exit;

});

$app->get('/forgot', function() {

	$page = new Page();

	$page->setTpl("forgot");

});

$app->post('/forgot', function() {

	$user = User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

$app->get('/forgot/sent', function() {
	$page = new Page();

	$page->setTpl("forgot-sent");
});

$app->get('/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl("forgot-reset", array(
		'name' => $user["desperson"],
		'code' => $_GET["code"]
	));

});

$app->post('/forgot/reset', function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);
	$user->setPassword(User::getPasswordHash($_POST["password"]));

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

$app->get('/profile', function() {

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user' => $user->getValues(),
		'profileMsg' => User::getMsg(),
		'profileError' => User::getError()
	]);

});

$app->post('/profile', function() {

	User::verifyLogin(false);

	if (!isset($_POST['desperson']) || $_POST['desperson'] == '') {

		User::setError("Preencha seu nome");

		header("Location: /profile");
		exit;

	}

	if (!isset($_POST['desemail']) || $_POST['desemail'] == '') {

		User::setError("Preencha seu email");

		header("Location: /profile");
		exit;

	}

	$user = new User();

	$user->get((int)$_SESSION[User::SESSION]['iduser']);

	if ($_POST['desemail'] !== $user->getdesemail()) {

		if (User::checkLoginExists($_POST['desemail'])) {

			User::setError("Esse email já está cadastrado");

			header("Location: /profile");
			exit;

		}

	}

	$_POST['inadmin'] = (int)$user->getinadmin();
	$_POST['deslogin'] = $_POST['desemail'];
	$_POST['despassword'] = $user->getdespassword();

	$user->setData($_POST);

	$user->update();

	User::updateSessionValues($_POST);

	User::setMsg("Dados alterados com sucesso");

	header("Location: /profile");
	exit;

});