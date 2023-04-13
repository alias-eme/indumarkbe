<?php

namespace corsica\framework\usuarios;



class Menu extends \corsica\framework\utils\DbClientSimple
{
    public function tablename()
    {
        return "t_menu";
    }
    public function primarykey()
    {
        return "id";
    }

    /**
     * Master viene como objeto y menulocal como array
     * Para preparar el menú de administración master
     */
    public function listar()
    {
        $mm = new MasterMenu($this->env);
        $mastermenu = $mm->listar();

        $sql = "select * from t_menu order by id";
        $menu = $this->selectAsArray($sql);
        $masterids = [];

        foreach ($mastermenu as &$x) {
            $index = array_search($x->id, array_column($menu, 'id'));
            if ($index !== false) {
                $x->checked = '1';
                $x->notas = $menu[$index]['nombre'].' '.$menu[$index]['href'];
            }
            //aquí sólo se hace una lista de ids...
            array_push($masterids, $x->id);
        }
        $this->logger->info("lastE", ['kjk' => $mastermenu[count($mastermenu) - 1]]);

        //$this->logger->info("masterids", $masterids);
        $customItems = [];
        foreach ($menu as $y) {
            if (!in_array($y['id'], $masterids)) {
                $row = (object)[
                    "id" => $y['id'],
                    "idparent" => $y['idparent'],
                    "icon" => 'custom',
                    "nombre" => $y['nombre'],
                    "descripcion" => $y['descripcion'],
                    "href" => $y['href'],
                    "checked" => '1',
                ];
                $this->logger->debug("added", ["row" => $row]);
                array_push($customItems, $row);
            }
        }
        $mastermenu = array_merge($mastermenu, $customItems);
        $this->logger->info("finally", ["mastermenu" => $mastermenu]);

        return $mastermenu;
    }
    public function menu($idperfil)
    {
        //a son los menues hijos y b el menu padre o grupo
        $sql = " select a.nombre, a.href, a.descripcion, b.nombre as grupo          ";
        $sql .= "  from t_menu a                               ";
        $sql .= "  join t_perfil_menu c on c.idmenu = a.id     ";
        $sql .= "  join t_menu b on a.idparent = b.id          ";
        $sql .= " where a.idparent IS NOT NULL                      ";
        $sql .= "   and c.idperfil = " . $this->numero($idperfil);
        $sql .= " order by a.id ";
        $menu = $this->select($sql);
        if ($idperfil == 1) {
            $menumasteritem1 = (object)array("nombre" => "Compañías", "href" => "/companies", "grupo" => "Master");
            $menumasteritem2 = (object)array("nombre" => "Usuarios", "href" => "/users", "grupo" => "Master");
            $menumasteritem3 = (object)array("nombre" => "Menus", "href" => "/menus", "grupo" => "Master");
            //$menumasteritem4 = (object)array("nombre"=>"Parámetros","href"=>"/params","grupo"=>"Master");
            array_push($menu, $menumasteritem1, $menumasteritem2, $menumasteritem3);
        }
        //elementos que no están el en el menu pero tiene acceso 
        $nuevo = (object)array("nuevo" => "pedido", "href" => "/nuevo", "grupo" => "Oculto");
        $cotizacion = (object)array("nombre" => "pedido", "href" => "/pedido", "grupo" => "Oculto");
        $pedido = (object)array("nombre" => "cotizacion", "href" => "/cotizacion", "grupo" => "Oculto");
        $misdatos = (object)array("nombre" => "Mis Datos", "href" => "/misdatos", "grupo" => "Oculto");
        $about = (object)array("nombre" => "Acerca de", "href" => "/about", "grupo" => "Oculto");
        array_push($menu, $nuevo, $cotizacion, $pedido, $misdatos, $about);

        return $menu;
    }
    public function menutop($idperfil)
    {

        $sql = " select a.nombre, a.href ,a.descripcion    ";
        $sql .= "  from t_menu a                               ";
        $sql .= "  join t_perfil_menu c on c.idmenu = a.id     ";
        $sql .= " where a.idparent IS NULL                       ";
        $sql .= "   and c.idperfil = " . $idperfil;
        $sql .= " order by a.id ";
        $menu = $this->select($sql);
        if ($idperfil == 1) {
            $menumasteritem = (object)array("nombre" => "Master", "href" => "#");
            array_push($menu, $menumasteritem);
        }

        return $menu;
    }
    /**
     * Lista todos los perfiles con 0/1 asociado al perfil
     */
    public function listarConPerfil($idperfil)
    {

        $sql  = "select a.id, a.idparent, a.nombre,a.href, coalesce(round(b.idmenu/a.id,0),0) as checked ";
        $sql .= "  from t_menu a  ";
        $sql .= "       left join t_perfil_menu b ";
        $sql .= "         on a.id = b.idmenu ";
        $sql .= "   and b.idperfil = " . $this->numero($idperfil);
        $sql .= "   order by a.id";
        //return $sql;
        $menu = $this->select($sql);
        return $menu;
    }
    /**
     * Agrega o elimina un menú para un perfil
     */
    public function cambiaMenuPerfil($idperfil, $idmenu, $checked)
    {

        $sql = "delete from t_perfil_menu where idperfil = " . $this->numero($idperfil) . " and idmenu = " . $this->numero($idmenu) . " ";
        if ($checked == 1)
            $sql = "insert into t_perfil_menu ( idperfil, idmenu ) values ( " . $this->numero($idperfil) . "," . $this->numero($idmenu) . ")";
        $this->execute($sql);
        return 0;
    }


    public function guardar($item, $checked)
    {
        $this->logger->info("guardar o eliminar", ["item" => $item]);
        if ($checked == 1) {
            return $this->insertMenu($item);
        } else {
            return $this->eliminar($item->id);
        }
    }
    private function insertMenu($item)
    {

        $this->insertSimple($item, ['id', 'idparent', 'icon', 'nombre', 'href', 'descripcion']);
        return "DB GUARDADO";
    }

    /**
     * Para el combo desplegable de menues Padre
     */
    public function listarPadres()
    {
        $sql = "select * from t_menu where idparent IS NULL order by id";

        $menu = $this->selectAsArray($sql);
        return $menu;
    }

    public function eliminar($id)
    {
        $sql = "delete from t_menu where id = " . $id;

        $this->execute($sql);
        return "DB ELIMINADO";
    }
}
