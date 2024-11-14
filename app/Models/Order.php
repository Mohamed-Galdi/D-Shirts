<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['customer_id', 'number_of_items', 'status', 'tracking_number', 'total_tshirts', 'total_amount'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tshirts()
    {
        return $this->belongsToMany(Tshirt::class)
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function getTotalTshirts()
    {
        $this->loadMissing('tshirts');
        return $this->tshirts->sum('pivot.quantity');
    }

    public function getTotalAmount()
    {
        $this->loadMissing('tshirts');
        return $this->tshirts->sum(function ($tshirt) {
            return $tshirt->pivot->quantity * $tshirt->pivot->price;
        });
    }

}
