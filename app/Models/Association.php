<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Association extends Model
{
	use HasFactory;

	protected $fillable = [
		'name',
		'slug',
		'url',
		'image',
		'description',
		'is_active',
		'priority',
	];

	public function users()
	{
		return $this->hasMany(\App\User::class, 'association_id');
	}

	public function coupons()
	{
		return $this->hasMany(AssociationCoupon::class);
	}
}



