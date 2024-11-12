<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('country', 'like', '%' . $request->search . '%')
                ->orWhere('city', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%');
        })
        ->select('id', 'name', 'email', 'phone', 'country', 'city', 'address')
        ->paginate(10)
        ->withQueryString()
        ->through(function ($customer) {
            $customer->total_spent = $customer->totalSpent();
            $customer->total_tshirts_bought = $customer->totalTshirtsBought();
            return $customer;
        });
        $searchTerm = request()->get('search');
        return inertia('Admin/Customers', compact('customers', 'searchTerm'));
    }
}
