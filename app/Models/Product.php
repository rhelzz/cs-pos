<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'selling_price', 
        'cost_price', 
        'category_id', 
        'stock', 
        'description', 
        'active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
