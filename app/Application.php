<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

    public function component()
    {
        return $this->hasOne('App\Component', 'id', 'item_id');
    }

    public function supplier()
    {
        return $this->hasOne('App\Supplier', 'id', 'supplier_id');
    }


    public function applicationDetails()
    {
        return $this->hasMany('App\ApplicationDetail', 'application_id', 'id');
    }
}
