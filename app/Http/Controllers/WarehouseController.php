<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\models\Materials;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\models\ProductMaterials;
use App\models\Products;
use App\models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class WarehouseController extends Controller
{
    public $current_quantity = 30;
    public $number = 0;
    public $remainder = 0;
    public $timesCalled = 0;


    public function index(Request $request)
    {
        $mato = 24; //kg
        $model = Warehouse::all();


        return $model;
    }


    public function getCalculations()
    {
        for ($i=1;$i<=1;$i++) {
            $product[$i] = [
                'id' => $i,
                'product_qty' => 1,
                'product_name' => Products::find($i)
            ];

            $productMaterials = ProductMaterials::where('product_id', $product[$i]['id'])->get();

            foreach ($productMaterials as $key => $values) {
                $material_id[] = $values->material_name->material_name;
                $quantity = $product[$i]['product_qty'] * $values->quantity;
                $warehouse = Warehouse::where('material_id', $values->material_id)->get();
                foreach ($warehouse as $ware) {
                    if ($ware->remainder <= $quantity) {
                        $product[$key]['product_materials'][] =
                            [
                                "warehouse_id" => $ware->id,
                                "material_name" => Materials::find($ware->material_id) ['material_name'],
                                "qty" => $productMaterials[$key]['remainder'],
                                "price" => $ware->price,
                            ];

                        $quantity = $quantity - $productMaterials[$key]['remainder'];
                    }
                }

            }

        }

        return [
            $product,

        ];
    }


    public function getMaterials($product_id,$quantity=50)
    {
        $productMaterials = ProductMaterials::where(['product_id'=>$product_id])->get();

        foreach ($productMaterials as $material) {
            $product_name = $material->product_name->product_code;
            $result[] = $this->getMaterialsCompound($material->material_id,$material->quantity * $quantity);
            $important_materials[] = array(
                'materials'=>$material->material_name->material_name,
                'quantity'=>$material->quantity*$quantity
            );
        }
          return [
              'product'=>$product_name,
              'quantity'=>$quantity,
              'important_materials'=>$important_materials,
              'result'=>$result
          ];
    }


    public function getMaterialsCompound($material_id, $necessary_quantity)
    {

        $materials_in_warehouse = Warehouse::where(['material_id' => $material_id])->get();

        /*ombordagi kerakli xomashyolarni chiqarib olamiz*/
        $result = array(
            'now_avaiblable'=>0
        );
        foreach ($materials_in_warehouse as $key => $material) {
            if($result['now_avaiblable'] == $necessary_quantity) {
                continue;
            }

            /*agar bazada biz soragan qatorda yetarli miqdor topilmasa*/
            if ($material->remainder < $necessary_quantity) {

                $quantity_to_be_taken = $material->remainder-$result['now_avaiblable'];
                $result['now_avaiblable'] += $material->remainder;
//                $material->remainder=0;

                $result[] = array(
                    'warehouse_id'=>$material->id,
                    'material_id'=>$material->material_name->material_name,
                    'taken_quantity'=>$material->remainder,
                    'price'=>$material->price,
                    'left_in_warehouse'=>$material->remainder,
                    'now_avaiblable'=>$result['now_avaiblable']
                );
                unset($materials_in_warehouse[0]);
            } elseif ($material->remainder >= $necessary_quantity) {
                // kerakli miqdorni ajratib olamiz, oldin olingan bolsa, uni hisobga olamiz
                $quantity_to_be_taken = $necessary_quantity - $result['now_avaiblable'];
                $result['now_avaiblable']+=$quantity_to_be_taken;
                $material->remainder-=$quantity_to_be_taken;
                $result[] = array(
                    'warehouse_id'=>$material->id,
                    'material_id'=>$material->material_name->material_name,
                    'taken_quantity'=>$quantity_to_be_taken,
                    'price'=>$material->price,
                    'left_in_warehouse'=>$material->remainder,
                );
            }

        }
        /*if($current_quantity < $necessary_quantity) {
            $result[] = array(
                'warehouse_id'=>null,
                'material_id'=>$material->material_name->material_name,
                'need_quantity'=>$necessary_quantity - $current_quantity,
                'price'=>null,
            );
        }*/

        return [
//            'result'=>$result,
            'model'=>$materials_in_warehouse
        ];
    }


    public function recursive($id,$quantity)
    {
        $material_left = array(
            'material_id'=>$id,
            'left_in_warehouse'=>null
        );
// kerakli miqdo
        $all = DB::table('warehouse')
            ->where('material_id', '=', $id)
            ->sum('remainder');

        $goods = [
            'quantity_taken'=>null
        ];
        $warehouse = Warehouse::where(['material_id' => $id])->get();
        foreach ($warehouse as $house) {
            if($goods['quantity_taken'] == $quantity) {
                continue;
            }
            if ($house->remainder < $quantity) {
                $this->remainder+=$house->remainder;
                $material_left['left_in_warehouse']=0;
                $material_left['description']='all gone';
                $material_left['taken_from_id_warehouse']=$house->id;
                $goods[] = array(
                    'material'=>$house->material_id,
                    'material_name'=>$house->material_name->material_name,
                    'material_need'=>$house->product_materials->quantity*$quantity,
                    'quantity_taken'=>$house->remainder,
                    'price'=>$house->price,
                    'warehouse'=>$material_left
                );

            } elseif ($house->remainder >= $quantity) {
                if ($goods['quantity_taken'] > 0) {
                    $will_be_taken = ($quantity - $goods['quantity_taken']); // kerakli olinadigon miqdor
                    $the_remaining_in_warehouse = $house->remainder - $will_be_taken ;
                } else {
                    $the_remaining_in_warehouse = $house->remainder - $quantity ;
                    $will_be_taken = $quantity; // kerakli olinadigon miqdor

                }

                $house->remainder -=$the_remaining_in_warehouse;
//                $this->remainder+=$the_remaining;
                $material_left['left_in_warehouse']=$the_remaining_in_warehouse;
                $goods[] = array(
                    'material'=>$house->material_id,
                    'material_name'=>$house->material_name->material_name,
                    'material_need'=>$house->product_materials->quantity*$quantity,
                    'quantity_taken'=>$will_be_taken,
                    'price'=>$house->price,
                    'warehouse'=>$material_left
                );
            }

        }
       /* if ($all < $quantity) {
            $goods[] = array(
                'material'=>$house->material_id,
                'quantity'=>$quantity - $all,
                'price'=>null
            );
        }*/
         return $goods;
    }





}
