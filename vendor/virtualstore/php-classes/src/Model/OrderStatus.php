<?php

namespace virtualstore\Model;

use virtualstore\DB\Sql;

class OrderStatus {

    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;

    public static function listAll() {
        
        $sql = new Sql;

        return $sql->select("SELECT * FROM tb_ordersstatus ORDER BY desstatus");

    }

}