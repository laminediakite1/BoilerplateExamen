<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends BaseModel
{
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description'
    ];

    /**
     * Les rôles ayant cette permission
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Grouper les permissions par groupe
     */
    public static function grouped()
    {
        return static::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
    }
}