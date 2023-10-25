<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['image']; // Add other fillable fields as needed

    public function sub_category(){
        return $this->hasMany(SubCategory::class);
    }

}
