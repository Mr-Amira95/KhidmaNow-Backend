<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'key',
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
