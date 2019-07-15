<?php

use \virtualstore\Model\User;

function formatPrice($price){
   if (!$price > 0) $price = 0;
   return number_format($price, 2, ",", ".");
}

function checkLogin($inadmin = true) {
   return User::checkLogin($inadmin);
}

function getUserName() {
   $user = User::getFromSession();
   return $user->getdesperson();
}