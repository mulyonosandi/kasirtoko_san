<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Produk;
use Jackiedo\Cart\Cart;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // Instantiate an object of the Cart class
        $cart = new Cart();
        
        // Use the name method on the instantiated object
        $cart->name($request->user()->id);

        $cart->applyTax([
            'id' => 1,
            'rate' => 10,
            'title' => 'Pajak PPN 10%'
        ]);

        return $cart->getDetails()->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_produk' => ['required', 'exists:produks']
        ]);

        $produk = Produk::where('kode_produk', $request->kode_produk)->first();
        $cart = new Cart();
        $cart->name($request->user()->id);

        $cart->addItem([
            'id' => $produk->id,
            'title' => $produk->nama_produk,
            'quantity' => 1,
            'price' => $produk->harga
        ]);

        return response()->json(['message' => 'Berhasil ditambahkan.']);
    }

    public function update(Request $request, $hash)
    {
        $request->validate([
            'qty' => ['required', 'in:-1,1']
        ]);

        $cart = new Cart();
        $cart->name($request->user()->id);

        $item = $cart->getItem($hash);

        if (!$item) {
            return abort(404);
        }

        $cart->updateItem($item->getHash(), [
            'quantity' => $item->getQuantity() + $request->qty
        ]);

        return response()->json(['message' => 'Berhasil diupdate.']);
    }

    public function destroy(Request $request, $hash)
    {
        $cart = new Cart();
        $cart->name($request->user()->id);

        $cart->removeItem($hash);

        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function clear(Request $request)
    {
        $cart = new Cart();
        $cart->name($request->user()->id);

        $cart->destroy();

        return back();
    }
}
