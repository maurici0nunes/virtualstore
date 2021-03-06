<?php

namespace virtualstore\Model;

use \virtualstore\DB\Sql;
use \virtualstore\Model;

class Address extends Model {

    const ADDRESS_ERROR = "Address_error";

    public static function getCep($cep) {

        $cep = str_replace("-", "", $cep);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$cep/json/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;
        
    }

    public function loadFromCep($cep) {
        
        $data = Address::getCep($cep);

        if (isset($data['logradouro']) && $data['logradouro']) {

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($cep);

        }

    }

    public function save() {

		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
			':idaddress'=>$this->getidaddress(),
			':idperson'=>$this->getidperson(),
			':desaddress'=>utf8_decode($this->getdesaddress()),
			':descomplement'=>utf8_decode($this->getdescomplement()),
			':descity'=>utf8_decode($this->getdescity()),
			':desstate'=>utf8_decode($this->getdesstate()),
			':descountry'=>utf8_decode($this->getdescountry()),
			':deszipcode'=>$this->getdeszipcode(),
			':desdistrict'=>$this->getdesdistrict()
        ]);

		if (count($results) > 0) {

            $this->setData($results[0]);
            
		}

	}

    public static function setMsgError($msg) {

		$_SESSION[Address::ADDRESS_ERROR] = $msg;

	}

	public static function getMsgError() {

		$msg = (isset($_SESSION[Address::ADDRESS_ERROR])) ? $_SESSION[Address::ADDRESS_ERROR] : "";

		Address::clearMsgError();

		return $msg;

	}

	public static function clearMsgError() {

		$_SESSION[Address::ADDRESS_ERROR] = NULL;

	}

}