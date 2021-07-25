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
    public $array = array();

    public function test() {
        $this->array = array(
            'ip' => ['count' => 10, 'price'=> 100],
            'mato' => ['count' => 50, 'price'=> 150],
        );

        $ishlab_chiqarish =  array(
            'koylak' => [
                'ip' => 5,
                'mato' => 50,
            ],
            'shim' => [
                'ip' => 10,
                'mato' => 50,
            ],
        );

        $maxsulot_count = 10;
        $count = $this->array['ip']['count'];

        if ($maxsulot_count > $count) {
            $this->array['ip']['count'] = $count - $maxsulot_count;
        }
    }

    public function getMaterials($product_id=1,$quantity=80)
    {
        $productMaterials = ProductMaterials::where(['product_id'=>$product_id])->get();
        foreach ($productMaterials as &$material) {
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
        foreach ($materials_in_warehouse as $key => &$material) {
            if($result['now_avaiblable'] == $necessary_quantity) {
                continue;
            }

            /*agar bazada biz soragan qatorda yetarli miqdor topilmasa*/
            if ($material->remainder < $necessary_quantity) {
                    if($result['now_avaiblable'] > 0) {
                        $quantity_to_be_taken = (($necessary_quantity-$result['now_avaiblable'])) ;
                    } else {
                        $quantity_to_be_taken = ($material->remainder - $result['now_avaiblable']);

                    }
                $result['now_avaiblable'] += $quantity_to_be_taken;
                $result[] = array(
                    'test'=>$quantity_to_be_taken,
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
