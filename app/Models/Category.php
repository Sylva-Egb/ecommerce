<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image_url'];

    public function products()
    {
        return $this->hasMany(Product::class, 'categorie_id');
    }
}
?>