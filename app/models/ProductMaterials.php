<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class ProductMaterials extends Model
{
    protected $table = 'product_materials';

    public function product_name()
    {
        return $this->hasOne(Products::class,'id','product_id');
    }
    public function material_name()
    {
        return $this->hasOne(Materials::class,'id','material_id');
    }


}
