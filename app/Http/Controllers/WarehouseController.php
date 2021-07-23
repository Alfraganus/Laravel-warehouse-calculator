<?php

namespace App\Http\Controllers;

use App\models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class WarehouseController extends Controller
{
    public $koylak = 30;
    public $number = 65;
    public $remainder = 0;
    public $timesCalled = 0;


    public function index(Request $request)
    {
        $mato = 24; //kg
        $model = Warehouse::all();


        return $model;
    }


    public function getMaterials(Request $request)
    {
        $datas = $request['material'];
         foreach ($datas as $id) {
             $test[] = $this->recursive($id);
             $this->remainder=0;
         }
         return $test;
    }



    public function recursive($id)
    {
        $all = DB::table('warehouse')
            ->where('material_id', '=', $id)
            ->sum('remainder');

        $goods = [];
        $warehouse = Warehouse::where(['material_id' => $id])->get();
        foreach ($warehouse as $house) {
            if($this->remainder == $this->number) {
                continue;
            }
            if ($house->remainder <= $this->number) {
                $this->remainder+=$house->remainder;
                $goods[] = array(
                    'material'=>$house->material_id,
                    'quantity'=>$house->remainder,
                    'price'=>$house->price
                );

            } elseif ($house->remainder >= $this->number) {
                $the_remaining = $this->number - $this->remainder;
                $house->remainder -=$the_remaining;
                $this->remainder+=$the_remaining;
                $goods[] = array(
                    'material'=>$house->material_id,
                    'quantity'=>$the_remaining,
                    'price'=>$house->price
                );
            }

        }
        if ($all < $this->number) {
            $goods[] = array(
                'material'=>$house->material_id,
                'quantity'=>$this->number - $all,
                'price'=>null
            );
        }
         return $goods;
    }
}
