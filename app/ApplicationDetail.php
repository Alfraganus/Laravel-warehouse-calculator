<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicationDetail extends Model
{

    /**
     * Get the user associated with the ApplicationDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function application()
    {
        return $this->hasOne('App\Application', 'id', 'application_id');
    }

    public $timestamps= false;
}
