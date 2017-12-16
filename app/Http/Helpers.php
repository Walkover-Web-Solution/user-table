<?php

namespace App\Http;


class Helpers
{
    public static function aasort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = array_values($ret);
        return $array;
    }

    public static function orderArray($arrayToOrder, $keys)
    {

        foreach ($arrayToOrder as $val) {
            foreach ($keys as $key) {
                $inner_ordered[$key] = $val[$key];
            }
            $ordered[] = $inner_ordered;
        }

        return $ordered;
    }

    public static function orderDataArray($arrayToOrder, $keys)
    {
        foreach ($keys as $key) {
            $inner_ordered[$key] = $arrayToOrder[$key];
        }
        $inner_ordered['id'] = $arrayToOrder['id'];
        return json_decode(json_encode($inner_ordered));
    }

    public static function orderData($tableNames)
    {
        $newarr = $orderNeed = array();
        foreach ($tableNames['table_structure'] as $k => $v) {
            $newarr[$v['column_name']] = $v;
        }

        foreach ($newarr as $k => $v) {
            if ($v['display'] == 1)
                $orderNeed[] = $k;
        }
        return $orderNeed;
    }
}