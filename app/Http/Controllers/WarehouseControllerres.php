<?php

namespace App\Http\Controllers;

use App\models\Materials;
use App\models\ProductMaterials;
use App\models\Warehouse;
use App\models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class WarehouseController extends Controller
{

    public function getMaterials(Request $request)
    {

        $products = [
            [
                'id'=>1,
                'name'=>'Koylak',
                'quantity'=>30,
            ],
            [
                'id'=>2,
                'name'=>'Shim',
                'quantity'=>20,
            ]
        ];
        $warehouse = Warehouse::get()->toArray();
        foreach ($products as $product) {

//            $product_name = Products::where(['id' => $product])->first()->product_code;
            $productMaterials = ProductMaterials::where(['product_id' => $product['id']])->get()->toArray();
            $result[] = [
                $this->getAllMaterials(
                    $warehouse,
                    $productMaterials,
                    $product['quantity']
                )
            ];
        }
//        var_dump($warehouse);

    }

    public function getAllMaterials(&$warehouse,$productMaterials,$quantity)
    {
        if(!isset($quantity) || $quantity < 1) {
            return var_dump('Quantity must be used');
        }
        $result = [];
        foreach ($productMaterials as $material) {
            $productQuantity = $material['quantity'] * $quantity;
            var_dump($material['material_id'] . ' => ' .$productQuantity . 'ta kearak');
            $takenValue = 0;
            foreach ($warehouse as $fn)
            {
                if ($fn['material_id'] == $material['material_id']) {
                    $array_index = array_search($fn['id'], array_column($warehouse, 'id'));
                    if (
                        $fn['remainder'] > 0 &&
                        $fn['remainder'] >= $productQuantity
                    ) {
                        if($takenValue > 0) {
                            $productQuantity-=$takenValue;
                        }
                        $warehouse[$array_index]['remainder'] -= $productQuantity;
                        $result[] = array(
                            'warehouse_id' => $fn['id'],
                            'id' => $fn['material_id'],
                            'material_id' => $fn['material_id'],
                            'bizga_kerak' => $productQuantity,
                            'narxi' => $fn['price'],
                            'olindi' =>$productQuantity,
                            'left_in_warehouse' => $warehouse[$array_index]['remainder'],
                        );
                        $takenValue = 0;
                        break;
                    } else {
                        if($r)
                            $result[] = array(
                                'material_id' => $fn['material_id'],
                                'bizga_kerak' =>'barchasi',
                                'olindi' => $warehouse[$array_index]['remainder'],
                                'warehouse_id' => $fn['id'],
                                'price' => $fn['price'],
                                'left_in_warehouse' => 0,
                                'ozgartirialdi'=>$warehouse[$array_index]['remainder'],

                            );
                        $takenValue =$warehouse[$array_index]['remainder'];
                        $material['quantity'] -= $warehouse[$array_index]['remainder'];
                        $warehouse[$array_index]['remainder'] = 0;
                    }
                }
            }
        }
        var_dump($result);

    }




    public function getMaterialsCompound($material_id, $necessary_quantity,$productMaterialstest)
    {
        $materials = [];
        $sum = 0;
        $materials_in_warehouse = Warehouse::where(['material_id' => $material_id])->get();
        /*ombordagi kerakli xomashyolarni chiqarib olamiz*/
        $result = array(
            'now_avaiblable'=>0
        );
        foreach ($materials_in_warehouse as $key => &$material) {
            if($result['now_avaiblable'] == $necessary_quantity) {
                continue;
            }

            /*agar bazada biz soragan qatorda yetarli miqdor topilmasa*/
            if ($material->remainder < $necessary_quantity) {
                $theNeed = $necessary_quantity - $result['now_avaiblable'];
                if($material->remainder >= $theNeed)  {
                    $quantity_to_be_taken = $necessary_quantity-$result['now_avaiblable'];
                } else {
                    $quantity_to_be_taken = $material->remainder;
                }
                $result['now_avaiblable'] += $quantity_to_be_taken;
                $result[] = array(
                    'test'=>$quantity_to_be_taken,
                    'need'=>$theNeed,
                    'warehouse_id'=>$material->id,
                    'material_id'=>$material->material_name->material_name,
                    'taken_quantity'=>$quantity_to_be_taken,
                    'price'=>$material->price,
                    'now_avaiblable'=>$result['now_avaiblable'],
                );

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
        if($result['now_avaiblable']  < $necessary_quantity) {
            $result[] = array(
                'warehouse_id'=>null,
                'material_id'=>$material->material_name->material_name,
                'need_quantity'=>$necessary_quantity - $result['now_avaiblable'] ,
                'price'=>null,
            );
        }
        return [
            'result'=>$result,
        ];
    }



}
