<?php

namespace App\Http\Controllers;

use App\models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class WarehouseController extends Controller
{
    public $koylak = 30;
    public $number = 48;
    public $remainder = 0;
    public $timesCalled = 0;


    public function index()
    {
        $mato = 24; //kg
        $model = Warehouse::all();


        return $model;
    }

    public function recursive()
    {
        $all = DB::table('warehouse')
            ->where('material_id', '=', 1)
            ->sum('remainder');

        $goods = [];
        $warehouse = Warehouse::where(['material_id' => 1])->get();
        foreach ($warehouse as $house) {
            if ($house->remainder <= $this->number) {
                $this->remainder+=$house->remainder;
                $goods[] = array(
                    'product'=>'koylak',
                    'quantity'=>$house->remainder,
                    'price'=>$house->price
                );

            } elseif ($house->remainder >= $this->number) {
                $the_remaining = $this->number - $this->remainder;
                $house->remainder -=$the_remaining;
                $this->remainder+=$the_remaining;
                $goods[] = array(
                    'product'=>'koylak',
                    'quantity'=>$the_remaining,
                    'price'=>$house->price
                );
            }

        }
        if ($all < $this->number) {
            $goods[] = array(
                'product'=>'koylak',
                'quantity'=>$this->number-$all,
                'price'=>null
            );
        }
        echo "<pre>";
         var_dump(print_r($goods));
    }
}
