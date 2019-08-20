<?php

namespace virtualstore;

class Model
{

    private $values = [];

    public function __call($name, $args)
    {

        $method  = substr($name, 0, 3);
        $fielName = substr($name, 3, strlen($name));

        switch ($method) {

            case "get":
                return (isset($this->values[$fielName])) ? $this->values[$fielName] : NULL;
                break;
            case "set":
                $this->values[$fielName] = $args[0];
                break;
        }
    }

    public function setData($data = array())
    {

        foreach ($data as $key => $value) {

            $this->{"set" . $key}($value);
        }
    }

    public function getValues()
    {

        return $this->values;
    }

    public static function pagination($pagination, int $currentpage, int $lshowbtn, $search = '')
    {
        $pages = [];

        if ($pagination['pages'] > $lshowbtn) {
            $before = ceil(($lshowbtn - 1) / 2);
            $after = floor(($lshowbtn - 1) / 2);
            $start = (($currentpage - $before) > 0) ? ($currentpage - 1) - $before : 0;
            $end = ($currentpage + $after) < $pagination['pages'] ? $pagination['pages'] - (($pagination['pages'] - $currentpage) - $after) : $pagination['pages'];
            if ($pagination['pages'] - $currentpage < $after) $start = $start - ($after - ($pagination['pages'] - $currentpage));
            if ($currentpage <= $before) $end = $end + ($before - ($currentpage - 1));
        } else {
            $start = 0;
            $end = $pagination['pages'];
        }

        if ($currentpage - $lshowbtn > 0) {
            array_push($pages, [
                'href' => '/admin/users?' . http_build_query([
                    'page' => $currentpage - $lshowbtn,
                    'search' => $search
                ]),
                'text' => '<<'
            ]);
        }

        if ($currentpage - 1 > 0) {
            array_push($pages, [
                'href' => '/admin/users?' . http_build_query([
                    'page' => $currentpage - 1,
                    'search' => $search
                ]),
                'text' => '<'
            ]);
        }

        for ($p = $start; $p < $end; $p++) {
            array_push($pages, [
                'href' => '/admin/users?' . http_build_query([
                    'page' => $p + 1,
                    'search' => $search
                ]),
                'text' => $p + 1
            ]);
        }

        if ($currentpage + 1 <= $pagination['pages']) {
            array_push($pages, [
                'href' => '/admin/users?' . http_build_query([
                    'page' => $currentpage + 1,
                    'search' => $search
                ]),
                'text' => '>'
            ]);
        }

        if ($currentpage + $lshowbtn <= $pagination['pages']) {
            array_push($pages, [
                'href' => '/admin/users?' . http_build_query([
                    'page' => $currentpage + $lshowbtn,
                    'search' => $search
                ]),
                'text' => '>>'
            ]);
        }

        return $pages;
    }
}
