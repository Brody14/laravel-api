<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['type', 'technologies'];

    protected $fillable = [
        'title',
        'description',
        'slug',
        'customer',
        'url',
        'type_id',
        'user_id',
        'cover'
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTecIds()
    {
        return $this->technologies->pluck('id')->all();
    }

    public function getRelatedProjects()
    {
        return $this->type->projects()->where('id', '!=', $this->id)->get();
    }

    protected function coverPath(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => asset('storage/' . $attributes['cover'])
        );
    }

    protected $appends = ['cover_path'];
}
