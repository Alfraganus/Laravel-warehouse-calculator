<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table = 'warehouse';

    public function material_name()
    {
        return $this->hasOne(Materials::class,'id','material_id');
    }

    public function product_materials()
    {
        return $this->hasOne(ProductMaterials::class,'material_id','material_id');
    }


}
