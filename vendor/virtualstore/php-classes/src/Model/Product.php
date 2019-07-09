<?php

namespace virtualstore\Model;

use \virtualstore\DB\Sql;
use \virtualstore\Model;
use \virtualstore\Mailer;

class Product extends Model {

    public static function listAll(){
        
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }

    public static function checkList($list){

        foreach ($list as &$row) {

            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();

        }
        return $list;
    }

    public function save(){

        $sql = new Sql();

        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=> $this->getidproduct(),
            ":desproduct"=> $this->getdesproduct(),
            ":vlprice"=> $this->getvlprice(),
            ":vlwidth"=> $this->getvlwidth(),
            ":vlheight"=> $this->getvlheight(),
            ":vllength"=> $this->getvllength(),
            ":vlweight"=> $this->getvlweight(),
            ":desurl"=> $this->getdesurl()            
        ));

        $this->setData($results[0]);

    }

    public function get($idproduct){

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=> $idproduct
        ));

        $this->setData($results[0]);

    }

    public function delete(){

        $sql = new Sql();
        $results = $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=> $this->getidproduct()
        ));

        product::updateFile();

    }

    public function checkPhoto(){

        if(file_exists($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg")){
            $url = "/assets/site/img/".$this->getidproduct().".jpg";
        }else {
            $url = "/assets/site/img/notimg.jpg";
        }

        return $this->setdesphoto($url);

    }

    public function getValues(){

        $this->checkPhoto();
        
        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file){
        
        $extension = explode(".", $file["name"]);
        $extension = end($extension);

        switch ($extension) {
            case 'jpg':
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
            case 'png':
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
        }

        $dist = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.
                "assets".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR.
                "img".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg";

        imagejpeg($image, $dist);
        imagedestroy($image);

        $this->checkPhoto();

    }
}