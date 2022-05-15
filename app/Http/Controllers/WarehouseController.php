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
                'id' => 1,
                'name' => 'Koylak',
                'quantity' => 30,
            ],
            [
                'id' => 2,
                'name' => 'Shim',
                'quantity' => 20,
            ]
        ];
        $warehouse = Warehouse::get()->toArray();
        foreach ($products as $product) {
            $productMaterials = ProductMaterials::where(['product_id' => $product['id']])->get()->toArray();
            $result[] = [
                'product_name'=>$product['name'],
                'product_qty'=>$product['quantity'],
                'product_materials'=> $this->getAllMaterials(
                    $warehouse,
                    $productMaterials,
                    $product['quantity']
                )
            ];
        }
        return $result;

    }

    public function getAllMaterials(&$warehouse,$productMaterials,$quantity)
    {
        if(!isset($quantity) || $quantity < 1) {
            return var_dump('Quantity must be used');
        }
        $result = [];
        foreach ($productMaterials as $material) {
            $productQuantity = $material['quantity'] * $quantity;
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
                            'material_id' => $fn['material_id'],
                            'qty' =>$productQuantity,
                            'price' => $fn['price'],

                        );
                        $takenValue = 0;
                        break;
                    } elseif($fn['remainder'] > 0) {
                        $result[] = array(
                            'warehouse_id' => $fn['id'],
                            'material_id' => $fn['material_id'],
                            'qty' => $warehouse[$array_index]['remainder'],
                            'price' => $fn['price'],
                        );
                        $takenValue =$warehouse[$array_index]['remainder'];
                        $material['quantity'] -= $warehouse[$array_index]['remainder'];
                        $warehouse[$array_index]['remainder'] = 0;
                    }
                }
            }
        }
        return $result;
    }


}
